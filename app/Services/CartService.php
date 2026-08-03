<?php

namespace App\Services;

use App\Models\CartItem;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\Auth;

class CartService
{
    public function items()
    {
        return CartItem::where('user_id', Auth::id())
            ->with('variant')
            ->get();
    }

    public function add($variantId, $quantity = 1)
    {
        $item = CartItem::firstOrCreate([
            'user_id' => Auth::id(),
            'product_variant_id' => $variantId,
        ]);

        $item->quantity += $quantity;
        $item->save();
    }

    public function update($itemId, $quantity)
    {
        $item = CartItem::where('user_id', Auth::id())->findOrFail($itemId);
        $item->update(['quantity' => $quantity]);
    }

    public function remove($itemId)
    {
        CartItem::where('user_id', Auth::id())->findOrFail($itemId)->delete();
    }

    public function clear()
    {
        CartItem::where('user_id', Auth::id())->delete();
    }

    public function total()
    {
        return $this->items()->sum(fn($i) => $i->variant->price * $i->quantity);
    }
}
