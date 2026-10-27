<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    public const STATUS_NEW = 'new';
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_READY_FOR_PICKUP = 'ready_for_pickup';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';

    public const PAYMENT_UNRECORDED = 'unrecorded';
    public const PAYMENT_PENDING = 'pending';
    public const PAYMENT_PAID = 'paid';
    public const PAYMENT_REFUND_PENDING = 'refund_pending';
    public const PAYMENT_REFUNDED = 'refunded';

    public const ORDER_CREATED = 'created';
    public const ORDER_PROCESSING = 'processing';
    public const ORDER_READY_FOR_PICKUP = 'ready_for_pickup';
    public const ORDER_COMPLETED = 'completed';
    public const ORDER_CANCELLED = 'cancelled';
    public const ORDER_CANCELLATION = 'cancellation';

    protected $fillable = [
        'user_id', 'address_id', 'name', 'last_name', 'email', 'phone',
        'address', 'total', 'status', 'payment_status',
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
        if (! $this->isFinalized() && $this->cancellation_requested_at !== null) {
            return self::ORDER_CANCELLATION;
        }

        return match ($this->status) {
            self::STATUS_NEW => self::ORDER_CREATED,
            self::STATUS_PROCESSING => self::ORDER_PROCESSING,
            self::STATUS_READY_FOR_PICKUP => self::ORDER_READY_FOR_PICKUP,
            self::STATUS_COMPLETED => self::ORDER_COMPLETED,
            self::STATUS_CANCELLED => self::ORDER_CANCELLED,
        };
    }

    public function isFinalized(): bool
    {
        return in_array($this->status, [self::STATUS_COMPLETED, self::STATUS_CANCELLED], true);
    }
}
