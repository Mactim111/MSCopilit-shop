<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
//    protected $fillable = ['user_id', 'product_id', 'quantity'];
//
//    public function product(): \Illuminate\Database\Eloquent\Relations\BelongsTo
//    {
//        return $this->belongsTo(Product::class);
//    }
    protected $fillable = [
        'order_id', 'product_id', 'title', 'price', 'quantity', 'subtotal'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
