<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ProductVariant;
use App\Services\CartService;
use App\Models\CartItem;

class CartController extends Controller
{
    public function __construct(private CartService $cart) {}

    public function index()
    {   
        return view('cart.index', [
            'items' => $this->cart->items(),
            'formattedTotal' => $this->cart->formattedTotal(28, 28),
        ]);
    }

    public function add(ProductVariant $variant)
    {
        // Добавляем товар через сервис.
        // Сервис внутри себя сам проверит наличие (stock - reserved)
        $this->cart->add($variant->id);
        
        return back()->with(['success' => 'Товар добавлен в корзину', 'added' => true]);
    }

    public function update(Request $request, $id)
    {
        $request->validate(['quantity' => 'required|integer|min:1']);

        /**
         * КЛЮЧЕВОЙ МОМЕНТ:
         * Вызываем update из сервиса.
         * Если сервис вернет объект RedirectResponse (значит была ошибка лимита),
         * то мы возвращаем его пользователю.
         */
        $result = $this->cart->update($id, $request->quantity);

        // Если сервис вернул редирект (с ошибкой "Недоступно"), отдаем его пользователю
        if ($result instanceof \Illuminate\Http\RedirectResponse) {
            return $result;
        }

        // Если все прошло успешно — стандартное сообщение
        return back()->with('success', 'Количество обновлено');
    }

    public function remove($id)
    {
        $this->cart->remove($id);
        return back()->with('success', 'Товар удалён');
    }

    public function batchActions(Request $request)
    {
        $action = $request->input('action'); 
        $ids = $request->input('items', []); // Это массив ID отмеченных товаров

        // 1. Обновление (Enter в поле ввода)
        if ($action === 'update') {
            foreach ($request->input('quantities', []) as $id => $quantity) {
                $this->cart->update($id, $quantity);
            }
            return back()->with('success', 'Количество обновлено');
        }

        // 2. Удаление выбранных
        if ($action === 'delete') {
            foreach($ids as $id) { $this->cart->remove($id); }
            return back()->with('success', 'Выбранные товары удалены');
        }

        // 3. Оформление заказа (Берем ТОЛЬКО отмеченные ID)
        if ($action === 'checkout') {
            if (empty($ids)) {
                return back()->with('error', 'Выберите товары для оформления');
            }
            // Передаем массив ID в чекаут
            return redirect()->route('orders.checkout', ['items' => $ids]);
        }

        return back();
    }

}