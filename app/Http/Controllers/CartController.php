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
        $ids = $request->input('items', []);

        // ЕСЛИ ACTION ПУСТ, но пришли количества — значит это обновление
        if (empty($action) && $request->has('quantities')) {
            $action = 'update';
        }

        // 1. Массовое удаление
        if ($action === 'delete') {
            foreach($ids as $id) { $this->cart->remove($id); }
            return back()->with('success', 'Выбранные товары удалены');
        }

        // 2. Удаление одного товара
        if (str_starts_with($action, 'remove_')) {
            $itemId = str_replace('remove_', '', $action);
            $this->cart->remove($itemId);
            return back()->with('success', 'Товар удалён');
        }

        // 3. Обновление количества
        if ($action === 'update') {
            foreach ($request->input('quantities', []) as $id => $quantity) {
                $this->cart->update($id, $quantity);
            }
            return back()->with('success', 'Количество обновлено');
        }

        // 4. Оформление
        if ($action === 'checkout') {
            return redirect()->route('orders.checkout', ['items' => $ids]);
        }

        return back();
    }

}