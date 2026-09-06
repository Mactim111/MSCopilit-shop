<?php

namespace App\Services;

use App\Models\CartItem;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Collection;

class CartService
{
    /**
     * Получить все товары в корзине в виде коллекции объектов.
     * Мы "приводим" данные из сессии к виду модели CartItem для совместимости.
     */

    public function items(): \Illuminate\Support\Collection
    {
        if (Auth::check()) {
            return \App\Models\CartItem::where('user_id', Auth::id())->with('variant')->get();
        }

        // Логика для гостей
        $sessionCart = session()->get('cart', []); // [variant_id => quantity]
        $items = collect();

        foreach ($sessionCart as $variantId => $quantity) {
            $variant = \App\Models\ProductVariant::find($variantId);
            if ($variant) {
                // 1. Создаем объект модели
                $item = new \App\Models\CartItem([
                    'product_variant_id' => $variantId,
                    'quantity' => $quantity,
                ]);
                // ПРИСВАИВАЕМ ID для Blade:
                $item->id = $variantId; 
                // 2. Вручную устанавливаем связь (Relation), чтобы работал вызов $item->variant
                $item->setRelation('variant', $variant);
                // 3. Пушим в коллекцию уже готовый объект
                $items->push($item);
            }
        }
        return $items;
    }

    public function add($variantId, $quantity = 1)
    {
        if (Auth::check()) {
            $item = CartItem::firstOrCreate([
                'user_id' => Auth::id(),
                'product_variant_id' => $variantId,
            ]);
            $item->increment('quantity', $quantity);
        } else {
            $cart = session()->get('cart', []);
            $cart[$variantId] = ($cart[$variantId] ?? 0) + $quantity;
            session()->put('cart', $cart);
        }
    }

    public function update($itemId, $quantity)
    {
        if (Auth::check()) {
            CartItem::where('user_id', Auth::id())->findOrFail($itemId)->update(['quantity' => $quantity]);
        } else {
            // Для гостя $itemId — это на самом деле product_variant_id
            $cart = session()->get('cart', []);
            if (isset($cart[$itemId])) {
                $cart[$itemId] = $quantity;
                session()->put('cart', $cart);
            }
        }
    }

    public function remove($itemId)
    {
        if (Auth::check()) {
            CartItem::where('user_id', Auth::id())->findOrFail($itemId)->delete();
        } else {
            $cart = session()->get('cart', []);
            unset($cart[$itemId]);
            session()->put('cart', $cart);
        }
    }

    public function clear()
    {
        Auth::check() 
            ? CartItem::where('user_id', Auth::id())->delete() 
            : session()->forget('cart');
    }

    // возвращает число - калькуляцию итоговой стоимости заказа - вариантов товаров в корзине в выбранном количестве
    public function total()
    {   
    return $this->items()->sum(fn($i) => $i->variant->price * $i->quantity);
    }
    
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
    public function formattedTotal(int $wholeFontSize = 30, int $fractionFontSize = 19): string
    {  
        $total = $this->total(); // ← используем существующий метод   
        $formatted = number_format($total, 2, '.', ' ');
        [$whole, $fraction] = explode('.', $formatted);
        return "<span style=\"font-size: {$wholeFontSize}px;\">{$whole}</span><span style=\"font-size: {$fractionFontSize}px;\">.</span><span style=\"font-size: {$fractionFontSize}px;\">{$fraction}</span> <i class=\"nbrb-icon\">BYN</i>";
    }

    /**
     * Слияние корзины гостя с БД после входа
     */
    public function mergeAfterLogin()
    {
        $sessionCart = session()->get('cart', []);
        if (empty($sessionCart)) return;

        foreach ($sessionCart as $variantId => $quantity) {
            $this->add($variantId, $quantity);
        }

        session()->forget('cart');
    }

    /**
     * Проверить, есть ли конкретный вариант товара в корзине
     */
    public function has($variantId): bool
    {
        if (Auth::check()) {
            return CartItem::where('user_id', Auth::id())
                ->where('product_variant_id', $variantId)
                ->exists();
        }

        // Для гостей проверяем массив в сессии
        $cart = session()->get('cart', []);
        return isset($cart[$variantId]);
    }
}
