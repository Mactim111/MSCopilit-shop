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

    public const DELIVERY_PICKUP = 'pickup';
    public const DELIVERY_COURIER = 'courier';
    public const COURIER_DELIVERY_PRICE = 20.00;
    public const PICKUP_ADDRESS = 'Пункт выдачи заказов: г. Брест, ул. Московская, 210 (ТЦ «Корона», 1 этаж)';

    public const PAYMENT_CASH_ON_DELIVERY = 'cash_on_delivery';
    public const PAYMENT_ONLINE_CARD = 'online_card';
    public const PAYMENT_CARD_ON_DELIVERY_LEGACY = 'card_on_delivery';

    public const ORDER_CREATED = 'created';
    public const ORDER_PROCESSING = 'processing';
    public const ORDER_READY_FOR_PICKUP = 'ready_for_pickup';
    public const ORDER_COMPLETED = 'completed';
    public const ORDER_CANCELLED = 'cancelled';
    public const ORDER_CANCELLATION = 'cancellation';

    protected $fillable = [
        'user_id', 'address_id', 'name', 'last_name', 'email', 'phone',
        'address', 'delivery_method', 'delivery_price', 'payment_method',
        'total', 'status', 'payment_status',
    ];

    protected $casts = [
        'cancellation_requested_at' => 'datetime',
        'delivery_price' => 'decimal:2',
    ];

    public function items(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function address()
    {
        return $this->belongsTo(Address::class);
    }

    public static function deliveryPriceFor(string $deliveryMethod): float
    {
        return match ($deliveryMethod) {
            self::DELIVERY_PICKUP => 0.0,
            self::DELIVERY_COURIER => self::COURIER_DELIVERY_PRICE,
            default => throw new \InvalidArgumentException('Неизвестный способ доставки.'),
        };
    }

    public function getDeliveryMethodLabelAttribute(): string
    {
        return match ($this->delivery_method) {
            self::DELIVERY_PICKUP => 'Самовывоз из пункта выдачи заказов',
            self::DELIVERY_COURIER => 'Доставка курьером по адресу',
            default => 'Способ доставки не указан',
        };
    }

    public function getPaymentMethodLabelAttribute(): string
    {
        return match ($this->payment_method) {
            self::PAYMENT_CASH_ON_DELIVERY,
            self::PAYMENT_CARD_ON_DELIVERY_LEGACY => 'При получении',
            self::PAYMENT_ONLINE_CARD => 'Онлайн',
            default => 'Способ оплаты не указан',
        };
    }

    public function getPaymentMethodDescriptionAttribute(): string
    {
        return match ($this->payment_method) {
            self::PAYMENT_CASH_ON_DELIVERY,
            self::PAYMENT_CARD_ON_DELIVERY_LEGACY => 'Наличными или банковской картой',
            self::PAYMENT_ONLINE_CARD => 'Банковской картой Visa, MasterCard, Белкарт и др.',
            default => 'Не указан',
        };
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

    public function getProfileStatusLabelAttribute(): string
    {
        return match ($this->profile_status) {
            self::ORDER_CREATED => 'Создан',
            self::ORDER_PROCESSING => 'Комплектация',
            self::ORDER_READY_FOR_PICKUP => 'Готов к выдаче',
            self::ORDER_CANCELLATION => 'Отправлен на отмену',
            self::ORDER_COMPLETED => 'Завершён',
            self::ORDER_CANCELLED => 'Отменён',
        };
    }

    public function isFinalized(): bool
    {
        return in_array($this->status, [self::STATUS_COMPLETED, self::STATUS_CANCELLED], true);
    }
}
