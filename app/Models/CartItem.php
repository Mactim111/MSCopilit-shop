<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CartItem extends Model
{

    protected $fillable = ['user_id', 'product_variant_id', 'quantity'];

    // связь с ProductVariant
    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }
    
    // public function getSubtotalAttribute()
    // {
    //     return $this->variant->price * $this->quantity;
    // }

    
    /**
     * Форматированная "ТЕКУЩАЯ!" стоимость за всё количество ОДНОГО ВАРИАНТА ТОВАРА (для КАРТОЧКИ варианта товара корзины)
     *  с управляемыми размерами шрифта для целой и дробной части - заодно появляется новое обозначение валюты РБ из шрифта!
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
    public function formattedSubtotal(int $wholeFontSize = 30, int $fractionFontSize = 19): string
    {
    
        $total = $this->variant->price * $this->quantity;
    // dd($total);   
        $subtotal = number_format($total, 2, '.', ' ');
        [$whole, $fraction] = explode('.', $subtotal);
        return "<span style=\"font-size: {$wholeFontSize}px;\">{$whole}</span><span style=\"font-size: {$fractionFontSize}px;\">.</span><span style=\"font-size: {$fractionFontSize}px;\">{$fraction}</span><i class=\"nbrb-icon\">BYN</i>";
    }

    /**
     * АНАЛОГИЧНО - Форматированная "СТАРАЯ!" стоимость за всё количество ОДНОГО ВАРИАНТА ТОВАРА (для КАРТОЧКИ варианта товара корзины)
     *  с управляемыми размерами шрифта для целой и дробной части - заодно появляется новое обозначение валюты РБ из шрифта!
     */
    /**
     * Форматированная "старая" стоимость за всё количество (для корзины)
     */
    public function formattedOldSubtotal(int $wholeFontSize = 14, int $fractionFontSize = 14): string
    {
        // 1. Считаем общую старую стоимость
        $totalOldPrice = $this->variant->old_price * $this->quantity;

        // 2. Проверяем, есть ли вообще старая цена
        if ($totalOldPrice <= 0) {
            return '';
        }

        // 3. Форматируем число вручную (так как мы не можем использовать 
        //    твой старый formattedOldPrice из ProductVariant, 
        //    потому что он жестко привязан к $this->old_price)
        $formatted = number_format($totalOldPrice, 2, '.', ' ');
        [$whole, $fraction] = explode('.', $formatted);

        return "<span style=\"font-size: {$wholeFontSize}px;\">{$whole}</span><span style=\"font-size: {$fractionFontSize}px;\">.</span><span style=\"font-size: {$fractionFontSize}px;\">{$fraction}</span> <i class=\"nbrb-icon\">BYN</i>";
    }
}
