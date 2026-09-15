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
use App\Models\CartItem;

class OrderController extends Controller
{
    public function __construct(private CartService $cart) {}

    /**
     * Страница оформления заказа
     */
    public function checkout(Request $request)
    {
        // Получаем массив ID из GET-параметров (например, ?items[]=6&items[]=7)
        $selectedIds = $request->query('items', []); 
        
        // Получаем товары из сервиса
        $allCartItems = $this->cart->items();

        // Фильтруем коллекцию
        $items = count($selectedIds) > 0 
            ? $allCartItems->whereIn('id', $selectedIds) 
            : $allCartItems;

        if ($items->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Корзина пуста или товары не выбраны');
        }

        // Считаем сумму ТОЛЬКО для выбранных товаров
        $total = $items->sum(fn($i) => $i->variant->price * $i->quantity);

        return view('orders.checkout', [
            'items'            => $items,
            'selectedIds'      => $selectedIds, // ПЕРЕДАЕМ ID В ШАБЛОН!
            'total'            => $total,
            'addresses'        => Auth::user()?->addresses()->latest()->get() ?? collect(),
            'defaultAddressId' => Auth::user()?->addresses()->latest()->first()->id ?? null,
        ]);
    }


    /**
     * Создание заказа
     */
    public function store(Request $request)
    {
        // 1. Получаем ID из формы
        $selectedIds = $request->input('items', []);
        $allCartItems = $this->cart->items();

        // Фильтруем товары для заказа
        $items = count($selectedIds) > 0 
            ? $allCartItems->whereIn('id', $selectedIds) 
            : $allCartItems;

        if ($items->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Корзина пуста или товары не выбраны!');
        }

        // ВАЖНО: Получаем ID тех записей, которые мы реально собираемся удалить
        $idsToDelete = $items->pluck('id')->toArray();

        $data = $request->validate([
            'address_id' => 'nullable|exists:addresses,id',
            'address'    => 'nullable|required_without:address_id|string|max:500',
            'name'       => 'required|string|max:255',
            'email'      => 'required|email',
            'phone'      => 'required|string|max:50',
        ]);

        $order = DB::transaction(function () use ($items, $data, $idsToDelete) {
            $user = Auth::user();
            $addressText = $data['address'];

            if ($user && !empty($data['address_id'])) {
                $address = $user->addresses()->findOrFail($data['address_id']);
                $addressText = $address->address_line; 
            } 

            $order = Order::create([
                'user_id'    => $user?->id,
                'address_id' => $data['address_id'] ?? null,
                'name'       => $data['name'],
                'email'      => $data['email'],
                'phone'      => $data['phone'],
                'address'    => $addressText,
                'total'      => $this->cart->total(), // ВНИМАНИЕ: тут $this->cart->total() посчитает ВСЮ корзину!
                // Лучше считать сумму из $items:
                'total'      => $items->sum(fn($i) => $i->variant->price * $i->quantity),
                'status'     => 'new',
            ]);

            foreach ($items as $item) {
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

            // УДАЛЯЕМ ТОЛЬКО ВЫБРАННОЕ
            $this->cart->removeMany($idsToDelete);

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
