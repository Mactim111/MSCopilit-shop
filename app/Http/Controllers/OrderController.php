<?php

namespace App\Http\Controllers;

use App\Exceptions\CartOrderAvailabilityException;
use App\Mail\OrderClient;
use App\Mail\OrderManager;
use App\Models\Address;
use App\Models\Order;
use App\Services\CartService;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    public function __construct(
        private CartService $cart,
        private OrderService $orders
    ) {}

    /**
     * Страница оформления заказа
     */
    public function checkout(Request $request)
    {
        // Получаем ID выбранных вариантов из формы корзины.
        $selectedIds = $this->normalizeIds($request->query('items', []));
        $allCartItems = $this->cart->items();

        // Оформление возможно только для явно выбранных товаров.
        $items = $allCartItems->whereIn('id', $selectedIds);

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
            'defaultAddressId' => Auth::user()?->addresses()->latest()->first()?->id,
        ]);
    }


    /**
     * Создание заказа
     */
    public function store(Request $request)
    {
        // В заказ попадают только ID, переданные чекбоксами корзины.
        $selectedIds = $this->normalizeIds($request->input('items', []));

        if (empty($selectedIds)) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Корзина пуста или товары не выбраны.'], 422);
            }

            return redirect()->route('cart.index')->with('error', 'Корзина пуста или товары не выбраны!');
        }

        $data = $request->validate([
            'address_id' => 'nullable|exists:addresses,id',
            'address'    => 'nullable|required_without:address_id|string|max:500',
            'name'       => 'required|string|max:255',
            'email'      => 'required|email',
            'phone'      => 'required|string|max:50',
        ]);

        try {
            $order = $this->orders->createFromCart($selectedIds, $data);
        } catch (CartOrderAvailabilityException $exception) {
            if ($request->expectsJson()) {
                return response()->json(['message' => $exception->getMessage()], 422);
            }

            return back()
                ->withInput()
                ->with('error', $exception->getMessage())
                ->with('error_link', route('cart.index'));
        }

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
        
        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Заказ успешно оформлен',
                'order_id' => $order->id,
                'redirect' => route('orders.thanks', ['order' => $order->id]),
            ]);
        }

        return redirect()->route('orders.thanks', ['order' => $order->id]);
    }

    /**
     * Приводит ID из query/form к безопасному уникальному массиву целых чисел.
     */
    private function normalizeIds(mixed $ids): array
    {
        return collect(is_array($ids) ? $ids : [$ids])
            ->filter(fn ($id) => is_scalar($id) && ctype_digit((string) $id))
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();
    }

    /**
     * Страница "Спасибо за заказ"
     */
    public function thanks(Order $order)
    {
        return view('orders.thanks', compact('order'));
    }
}
