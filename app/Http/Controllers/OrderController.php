<?php

namespace App\Http\Controllers;

use App\Models\Address;
use App\Models\Order;
use App\Models\OrderItem;
use App\Services\CartService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function __construct(private CartService $cart) {}

    /**
     * Страница оформления заказа
     */
    public function checkout()
    {
        $items = $this->cart->items();

        if ($items->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Корзина пуста');
        }

        $user = Auth::user();

        // Все адреса пользователя (последний — первый)
        $addresses = $user->addresses()->latest()->get();

        // По умолчанию выбираем последний сохранённый адрес
        $defaultAddressId = $addresses->first()->id ?? null;

        return view('orders.checkout', [
            'items'            => $items,
            'total'            => $this->cart->total(),
            'addresses'        => $addresses,
            'defaultAddressId' => $defaultAddressId,
        ]);
    }

    /**
     * Создание заказа
     */
    public function store(Request $request)
    {
        $items = $this->cart->items();
        if ($items->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Корзина пуста');
        }

        /**
         * Валидация:
         * - address обязателен, если НЕ выбран address_id
         * - address_id должен существовать в таблице addresses
         */
        $data = $request->validate([
            'address_id' => 'nullable|exists:addresses,id',
            'address'    => 'nullable|required_without:address_id|string|max:500',
            'name'       => 'required|string|max:255',
            'email'      => 'required|email',
            'phone'      => 'required|string|max:50',
        ]);
//        dd($data);

        $order = DB::transaction(function () use ($items, $data) {

            $user = Auth::user();
            $addressId = $data['address_id'] ?? null;

            /**
             * Если выбран существующий адрес
             */
            if ($addressId) {
                $address = $user->addresses()->findOrFail($addressId);

                $addressText = "{$address->address_line}, {$address->city}, {$address->state} {$address->zip}, {$address->country}";
            }

            /**
             * Если выбран новый адрес
             */
            else {
                $addressText = $data['address'];

                $new = $user->addresses()->create([
                    'label'        => 'Новый адрес',
                    'address_line' => $addressText,
                    'city'         => '—',
                    'state'        => null,
                    'zip'          => null,
                    'country'      => '—',
                ]);

                $addressId = $new->id;
            }

            /**
             * Создание заказа
             */
            $order = Order::create([
                'user_id'    => $user->id,
                'address_id' => $addressId,
                'name'       => $data['name'],
                'email'      => $data['email'],
                'phone'      => $data['phone'],
                'address'    => $addressText,
                'total'      => $this->cart->total(),
                'status'     => 'new',
            ]);

            /**
             * Создание позиций заказа
             */
            foreach ($items as $item) {
                OrderItem::create([
                    'order_id'   => $order->id,
                    'product_variant_id' => $item->product_variant_id,
                    'title'      => $item->variant->title,
                    'price'      => $item->variant->price,
                    'quantity'   => $item->quantity,
                    'subtotal'   => $item->variant->price * $item->quantity,
                ]);
            }

            $this->cart->clear();

            return $order;
        });

        return redirect()->route('orders.thanks', ['order' => $order->id]);
    }

    /**
     * Страница "Спасибо за заказ"
     */
    public function thanks(Order $order)
    {
        return view('orders.thanks', compact('order'));
    }
}
