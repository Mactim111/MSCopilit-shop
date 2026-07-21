<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CartItem extends Model
{
//    protected $fillable = [
//        'order_id', 'product_id', 'title', 'price', 'quantity', 'subtotal'
//    ];
//
//    public function order(): \Illuminate\Database\Eloquent\Relations\BelongsTo
//    {
//        return $this->belongsTo(Order::class);
//    }

    protected $fillable = ['user_id', 'product_variant_id', 'quantity'];

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class);
    }
    public function getSubtotalAttribute()
    {
        return $this->variant->price * $this->quantity;
    }
}
