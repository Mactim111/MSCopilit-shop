<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
//    protected $fillable = [
//        'user_id', 'status', 'name', 'email', 'phone', 'address', 'total'
//    ];
//
//    public function items(): \Illuminate\Database\Eloquent\Relations\HasMany
//    {
//        return $this->hasMany(OrderItem::class);
//    }
//
//    public function address(): \Illuminate\Database\Eloquent\Relations\BelongsTo
//    {
//        return $this->belongsTo(Address::class);
//    }
    protected $fillable = [
        'user_id', 'address_id', 'name', 'email', 'phone',
        'address', 'total', 'status'
    ];

    public function items(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function address()
    {
        return $this->belongsTo(Address::class);
    }
    
}
