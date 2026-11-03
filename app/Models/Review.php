<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Review extends Model
{
    use HasFactory;
    use SoftDeletes;

    public const MODERATION_PENDING = 'pending';
    public const MODERATION_APPROVED = 'approved';
    public const MODERATION_REJECTED = 'rejected';
    public const MODERATION_STATUSES = [
        self::MODERATION_PENDING,
        self::MODERATION_APPROVED,
        self::MODERATION_REJECTED,
    ];

    protected $fillable = [
        'user_id',
        'product_variant_id',
        'order_id',
        'moderation_status',
        'rating',
        'advantages',
        'disadvantages',
        'comment',
        'addition',
        'addition_updated_at',
        'addition_deleted_at',
        'addition_moderation_status',
        'addition_is_published',
        'is_published',
    ];

    protected $casts = [
        'rating' => 'integer',
        'is_published' => 'boolean',
        'addition_is_published' => 'boolean',
        'addition_updated_at' => 'datetime',
        'addition_deleted_at' => 'datetime',
    ];

    protected $attributes = [
        'moderation_status' => self::MODERATION_PENDING,
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id')->withTrashed();
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ReviewImage::class)->orderBy('position');
    }

    public function reply(): HasOne
    {
        return $this->hasOne(ReviewReply::class);
    }

    public function votes(): HasMany
    {
        return $this->hasMany(ReviewVote::class);
    }

    public function scopeOrderByHelpfulness(Builder $query): Builder
    {
        return $query->orderByRaw(
            '(SELECT COUNT(*) FROM review_votes AS helpful_votes WHERE helpful_votes.review_id = reviews.id AND helpful_votes.type = ?) - (SELECT COUNT(*) FROM review_votes AS unhelpful_votes WHERE unhelpful_votes.review_id = reviews.id AND unhelpful_votes.type = ?) DESC',
            [ReviewVote::TYPE_LIKE, ReviewVote::TYPE_DISLIKE]
        );
    }
}
