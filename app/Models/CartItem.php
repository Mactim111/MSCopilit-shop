<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CartItem extends Model
{

    protected $fillable = ['user_id', 'product_variant_id', 'quantity'];

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }
    // public function getSubtotalAttribute()
    // {
    //     return $this->variant->price * $this->quantity;
    // }

    
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
    public function formattedSubtotal(int $wholeFontSize = 30, int $fractionFontSize = 19): string
    {
    
        $total = $this->variant->price * $this->quantity;
    // dd($total);   
        $subtotal = number_format($total, 2, '.', ' ');
        [$whole, $fraction] = explode('.', $subtotal);
        return "<span style=\"font-size: {$wholeFontSize}px;\">{$whole}</span><span style=\"font-size: {$fractionFontSize}px;\">.</span><span style=\"font-size: {$fractionFontSize}px;\">{$fraction}</span> <i class=\"nbrb-icon\">BYN</i>";
    }
}
