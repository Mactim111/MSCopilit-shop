<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ProductVariant;
use App\Services\CartService;
use App\Models\CartItem;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    public function __construct(private CartService $cart) {}

    public function index()
    {   
    return view('cart.index', [
            'items' => $this->cart->items(),
            'total' => $this->cart->total(),
        ]);
    }

    public function add(ProductVariant $variant)
    {
        $this->cart->add($variant->id);
        return back()->with(['success'=> 'Товар добавлен в корзину', 'added' => true]);
    }

    public function update(Request $request, CartItem $item)
    {
        $request->validate(['quantity' => 'required|integer|min:1']);
        $this->cart->update($item->id, $request->quantity);

        return back()->with('success', 'Количество обновлено');
    }

    public function remove(CartItem $item)
    {
        $this->cart->remove($item->id);
        return back()->with('success', 'Товар удалён');
    }
}
