<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Review extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'product_variant_id',
        'rating',
        'advantages',
        'disadvantages',
        'comment',
        'addition',
        'addition_updated_at',
        'addition_is_published',
        'is_published',
    ];

    protected $casts = [
        'rating' => 'integer',
        'is_published' => 'boolean',
        'addition_is_published' => 'boolean',
        'addition_updated_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id')->withTrashed();
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
}
