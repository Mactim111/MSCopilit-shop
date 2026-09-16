<?php

namespace App\Http\Controllers;

use App\Exceptions\CartQuantityUnavailableException;
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
        $data = $request->validate(['quantity' => 'required|integer|min:1']);

        try {
            $this->cart->update($id, $data['quantity']);
        } catch (CartQuantityUnavailableException $exception) {
            if ($request->expectsJson()) {
                return response()->json(['message' => $exception->getMessage()], 422);
            }

            return back()->with('error', $exception->getMessage());
        }

        if ($request->expectsJson()) {
            return $this->cartJsonResponse('Количество обновлено');
        }

        return back()->with('success', 'Количество обновлено');
    }

    public function remove($id)
    {
        $this->cart->remove($id);

        if (request()->expectsJson()) {
            return $this->cartJsonResponse('Товар удалён');
        }

        return back()->with('success', 'Товар удалён');
    }

    public function batchActions(Request $request)
    {
        $action = $request->string('action')->toString();
        $ids = collect($request->input('items', []))
            ->filter(fn ($id) => is_scalar($id) && ctype_digit((string) $id))
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();

        if ($action === 'delete') {
            if (empty($ids)) {
                return back()->with('error', 'Выберите товары для удаления');
            }

            $this->cart->removeMany($ids);

            if ($request->expectsJson()) {
                return $this->cartJsonResponse('Выбранные товары удалены');
            }

            return back()->with('success', 'Выбранные товары удалены');
        }

        if ($action === 'checkout') {
            if (empty($ids)) {
                return back()->with('error', 'Выберите товары для заказа');
            }

            return redirect()->route('orders.checkout', ['items' => $ids]);
        }

        return back()->with('error', 'Неизвестное действие корзины');
    }

    private function cartJsonResponse(string $message)
    {
        return response()->json([
            'message' => $message,
            'count' => $this->cart->count(),
            'total' => $this->cart->total(),
        ]);
    }

}