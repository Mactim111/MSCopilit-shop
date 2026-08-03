<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id', 'product_variant_id', 'title', 'price', 'quantity', 'subtotal'
    ];

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    // связь на Order
    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function getFormattedSubtotalAttribute()
    {
        return number_format($this->subtotal, 2, '.', ' ');
    }

    /**
     * Форматированная цена с управляемыми размерами шрифта для целой и дробной части.
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
        $subtotal = number_format($this->subtotal, 2, '.', ' ');
        [$whole, $fraction] = explode('.', $subtotal);

        return "<span style=\"font-size: {$wholeFontSize}px;\">{$whole}</span><span style=\"font-size: {$fractionFontSize}px;\">.</span><span style=\"font-size: {$fractionFontSize}px;\">{$fraction}</span> <i class=\"nbrb-icon\">BYN</i>";
    }

}
