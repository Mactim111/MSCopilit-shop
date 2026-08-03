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

    public function formattedTotal(int $wholeFontSize = 30, int $fractionFontSize = 19): string
    {
    
    /**
     * Форматированная цена с управляемыми размерами шрифта для целой и дробной части - заодно появляется новое обозначение валюты РБ из шрифта!
     * 
     * @param int $wholeFontSize Размер шрифта целой части (в px), по умолчанию 30
     * @param int $fractionFontSize Размер шрифта дробной части (в px), по умолчанию 19
     * @return string HTML строка с ценой
     * 
     * Примеры использования в шаблоне:
     * {!! $variant->formattedPrice(30, 19) !!}  // целая 30px, дробная 19px
     * {!! $variant->formattedPrice(28, 17) !!}  // целая 28px, дробная 17px
     * {!! $variant->formattedPrice() !!}        // использует значения по умолчанию
     */   
    $totalSum = $this->items()->sum(fn($i) => $i->variant->price * $i->quantity);
    // dd($total);   
        $total = number_format($totalSum, 2, '.', ' ');
        [$whole, $fraction] = explode('.', $total);
        return "<span style=\"font-size: {$wholeFontSize}px;\">{$whole}</span><span style=\"font-size: {$fractionFontSize}px;\">.</span><span style=\"font-size: {$fractionFontSize}px;\">{$fraction}</span> <i class=\"nbrb-icon\">BYN</i>";
    }
}
