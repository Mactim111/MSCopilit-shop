<?php

namespace App\Services;

use App\Exceptions\CartOrderAvailabilityException;
use App\Models\Address;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ProductVariant;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderService
{
    public function __construct(private CartService $cart) {}

    /**
     * Создаёт заказ из выбранных позиций и атомарно резервирует их остаток.
     */
    public function createFromCart(array $selectedIds, array $data): Order
    {
        $cartItems = $this->cart->items()->whereIn('id', $selectedIds);

        if ($cartItems->isEmpty()) {
            throw ValidationException::withMessages([
                'items' => 'Корзина пуста или товары не выбраны.',
            ]);
        }

        $idsToDelete = $cartItems->pluck('id')->all();

        return DB::transaction(function () use ($cartItems, $data, $idsToDelete) {
            $user = Auth::user();
            $items = $this->reloadItemsInsideTransaction($cartItems, $idsToDelete, $user?->id);
            $deliveryMethod = $data['delivery_method'];
            $addressText = $deliveryMethod === Order::DELIVERY_PICKUP
                ? Order::PICKUP_ADDRESS
                : ($data['address'] ?? null);
            $addressId = null;

            if ($deliveryMethod === Order::DELIVERY_COURIER && $user && !empty($data['address_id'])) {
                $address = $user->addresses()->findOrFail($data['address_id']);
                $addressText = $address->fullAddress();
                $addressId = $address->id;
            }

            $deliveryPrice = Order::deliveryPriceFor($deliveryMethod);
            $goodsTotal = $items->sum(fn ($item) => $item->variant->price * $item->quantity);

            $order = Order::create([
                'user_id' => $user?->id,
                'address_id' => $addressId,
                'name' => $data['name'],
                'last_name' => $data['last_name'] ?? null,
                'email' => $data['email'],
                'phone' => $data['phone'],
                'address' => $addressText,
                'delivery_method' => $deliveryMethod,
                'delivery_price' => $deliveryPrice,
                'payment_method' => $data['payment_method'],
                'total' => $goodsTotal + $deliveryPrice,
                'status' => Order::STATUS_NEW,
            ]);

            foreach ($items as $item) {
                $item->variant->increment('reserved', $item->quantity);

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_variant_id' => $item->product_variant_id,
                    'title' => $item->variant->title,
                    'price' => $item->variant->price,
                    'quantity' => $item->quantity,
                    'subtotal' => $item->variant->price * $item->quantity,
                ]);
            }

            $this->cart->removeMany($idsToDelete);

            return $order;
        });
    }

    private function reloadItemsInsideTransaction(Collection $cartItems, array $ids, ?int $userId): Collection
    {
        $items = $userId
            ? CartItem::where('user_id', $userId)->whereIn('id', $ids)->with('variant')->get()
            : $cartItems;

        if ($items->count() !== count($ids)) {
            throw ValidationException::withMessages([
                'items' => 'Некоторые выбранные товары больше недоступны в корзине.',
            ]);
        }

        return $items->map(function ($item) {
            $variant = ProductVariant::whereKey($item->product_variant_id)
                ->lockForUpdate()
                ->first();
            $available = $variant ? $variant->stock - $variant->reserved : 0;

            if (!$variant || $available < $item->quantity) {
                $title = $item->variant?->title ?? 'Выбранный товар';

                throw new CartOrderAvailabilityException(
                    $title,
                    max(0, $available),
                    $item->quantity
                );
            }

            $item->setRelation('variant', $variant);
            return $item;
        });
    }
}
