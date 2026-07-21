<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductFilterIndex extends Model
{
    public $timestamps = false;

    protected $table = 'product_filter_index';

    protected $fillable = [
        'category_id',
        'product_id',
        'product_variant_id',
        'property_id',
        'value_slug',
        'numeric_value',
        'price',
    ];

    protected $casts = [
        'numeric_value' => 'decimal:2',
        'price'         => 'decimal:2',
    ];

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }
}
