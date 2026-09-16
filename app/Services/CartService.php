<?php

namespace App\Services;

use App\Exceptions\CartQuantityUnavailableException;
use App\Models\CartItem;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class CartService
{
    /**
     * Получить все товары в корзине в виде коллекции объектов.
     * Мы "приводим" данные из сессии к виду модели CartItem для совместимости.
     */

    public function items(): Collection
    {
        if (Auth::check()) {
            return CartItem::where('user_id', Auth::id())->with('variant')->get();
        }

        // Логика для гостей
        $sessionCart = session()->get('cart', []); // [variant_id => quantity]
        $items = collect();

        foreach ($sessionCart as $variantId => $quantity) {
            $variant = ProductVariant::find($variantId);
            if ($variant) {
                // 1. Создаем объект модели
                $item = new CartItem([
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


    // для логики действия кнопок «В корзину»
    // внутри add мы тоже используем min($quantity, $available), чтобы даже при быстром клике по кнопке "Добавить" в карточке нельзя было добавить больше, 
    // чем есть на складе
    public function add($variantId, $quantity = 1)
    {
        // Используем транзакцию, чтобы заблокировать строку товара в БД
        return DB::transaction(function () use ($variantId, $quantity) {
            
            // Блокируем строку товара для обновления (lockForUpdate)
            // Это предотвратит одновременную покупку одного и того же товара разными юзерами
            $variant = ProductVariant::where('id', $variantId)->lockForUpdate()->firstOrFail();
            
            $available = $variant->stock - $variant->reserved;

            // 1. ЖЕСТКАЯ ПРОВЕРКА: Если товара нет — выбрасываем исключение или просто выходим
            if ($available <= 0) {
                return; 
            }

            if (Auth::check()) {
                $item = CartItem::where('user_id', Auth::id())
                                ->where('product_variant_id', $variantId)
                                ->first();

                if ($item) {
                    // Вычисляем новое количество, но не больше доступного
                    $newQuantity = min($item->quantity + $quantity, $available);
                    $item->update(['quantity' => $newQuantity]);
                } else {
                    // Создаем с учетом доступности (min(запрос, доступное))
                    CartItem::create([
                        'user_id' => Auth::id(),
                        'product_variant_id' => $variantId,
                        'quantity' => min($quantity, $available)
                    ]);
                }
            } else {
                // Логика для сессии (тут сложнее залочить базу, 
                // поэтому полагаемся на актуальный $available, полученный выше)
                $cart = session()->get('cart', []);
                $currentQty = $cart[$variantId] ?? 0;
                $cart[$variantId] = min($currentQty + $quantity, $available);
                session()->put('cart', $cart);
            }
        });
    }

    // Обновление количества не зависит от HTTP: при недоступном остатке
    // сервис бросает исключение, а контроллер выбирает redirect или JSON-ответ.
    public function update($itemId, int $quantity): void
    {
        // Определяем variant и текущий item
        if (Auth::check()) {
            $item = CartItem::where('user_id', Auth::id())->findOrFail($itemId);
            $variant = $item->variant;
        } else {
            $variant = ProductVariant::find($itemId); // Для гостя ID - это variant_id
        }

        $available = $variant->stock - $variant->reserved;

        if ($quantity > $available) {
            throw new CartQuantityUnavailableException(max(0, $available));
        }

        // Обычное обновление
        if (Auth::check()) {
            $item->update(['quantity' => $quantity]);
        } else {
            $cart = session()->get('cart', []);
            $cart[$variant->id] = $quantity;
            session()->put('cart', $cart);
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

    //  метод, который удаляет товары по массиву ID
    public function removeMany(array $ids)
    {
        if (Auth::check()) {
            CartItem::where('user_id', Auth::id())->whereIn('id', $ids)->delete();
        } else {
            $cart = session()->get('cart', []);
            foreach ($ids as $id) {
                unset($cart[$id]);
            }
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

    /**
     * Возвращает количество товаров в корзине (сумма quantity всех товаров) длля отображения около ссылки-иконки КОРЗИНА в шапке сайта.
     */
    public function count(): int
    {
        // Используем наш метод items(), который уже умеет работать и с БД, и с сессией
        return $this->items()->sum('quantity');
    }
}
