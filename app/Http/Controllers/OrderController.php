<?php

namespace App\Http\Controllers;

use App\Mail\OrderClient;
use App\Mail\OrderManager;
use App\Models\Address;
use App\Models\Order;
use App\Models\OrderItem;
use App\Services\CartService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

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

        // Безопасное получение адресов: если гость — возвращаем пустую коллекцию
        $addresses = $user ? $user->addresses()->latest()->get() : collect();

        // По умолчанию выбираем последний сохранённый адрес (только для юзеров)
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
        // dd($data);

        $order = DB::transaction(function () use ($items, $data) {
            $user = Auth::user();
            $addressText = $data['address']; // Текст из textarea

            // Если это юзер и он выбрал адрес из списка (радиокнопка)
            if ($user && !empty($data['address_id'])) {
                $address = $user->addresses()->findOrFail($data['address_id']);
                $addressText = $address->address_line; 
            } 

            /**
             * Создание заказа
             */
            $order = Order::create([
                // 'user_id'    => $user->id,
                // 'address_id' => $addressId,
                'user_id'    => $user?->id, // Запишет ID или NULL
                'address_id' => $data['address_id'] ?? null,
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
                // КЛЮЧЕВОЙ МОМЕНТ: Резервируем товар
                $item->variant->increment('reserved', $item->quantity);
                
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

        // --- ПОДГОТОВКА ДАННЫХ ДЛЯ ПИСЬМА ---
        // Мы создаем плоский массив, который точно соответствует шаблону mail.order-client
        $cartForMail = $order->items->map(function($item) {
            return [
                'title'    => $item->title,
                'price'    => $item->price,
                'quantity' => $item->quantity,
            ];
        })->toArray();

        try {
            // Отправка клиенту
            Mail::to($order->email)->send(new OrderClient(
                $cartForMail, 
                $order->total, 
                $order->id
            ));

            // Отправка менеджеру (передаем ID заказа и адрес)
            Mail::to(config('mail.from.address'))->send(new OrderManager(
                $order->id,
                $order->address
            ));
        } catch (\Exception $e) {
            // Если почта не ушла, заказ все равно создан, просто логируем ошибку
            Log::error("Ошибка отправки почты: " . $e->getMessage());
        }
        
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
