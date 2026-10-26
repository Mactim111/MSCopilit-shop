<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    public const STATUS_NEW = 'new';
    public const STATUS_PAID = 'paid';
    public const STATUS_SHIPPED = 'shipped';
    public const STATUS_CANCELLED = 'cancelled';

    public const ORDER_CREATED = 'created';
    public const ORDER_COMPLETED = 'completed';
    public const ORDER_CANCELLED = 'cancelled';
    public const ORDER_CANCELLATION = 'cancellation';

    protected $fillable = [
        'user_id', 'address_id', 'name', 'last_name', 'email', 'phone',
        'address', 'total', 'status',
    ];

    protected $casts = [
        'cancellation_requested_at' => 'datetime',
    ];

    public function items(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function address()
    {
        return $this->belongsTo(Address::class);
    }

    public function getProfileStatusAttribute(): string
    {
        if (
            $this->status === self::STATUS_NEW
            && $this->cancellation_requested_at !== null
        ) {
            return self::ORDER_CANCELLATION;
        }

        return match ($this->status) {
            self::STATUS_NEW => self::ORDER_CREATED,
            self::STATUS_PAID, self::STATUS_SHIPPED => self::ORDER_COMPLETED,
            self::STATUS_CANCELLED => self::ORDER_CANCELLED,
        };
    }
}
