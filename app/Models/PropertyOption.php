<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class PropertyOption extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = ['property_id', 'value', 'slug', 'excerpt', 'numeric_value', 'color_hex', 'position'];

    protected $casts = [
        'numeric_value' => 'decimal:2',
        'position'      => 'integer',
    ];

    // ------------------------------------------------------------------
    // Отношения
    // ------------------------------------------------------------------
    
    public function property()
    {
        return $this->belongsTo(Property::class);
    }
    
    public function variants()
    {
        return $this->belongsToMany(ProductVariant::class, 'product_variant_property_options', 'property_option_id', 'product_variant_id');
    }

    // ------------------------------------------------------------------
    // Хелперы
    // ------------------------------------------------------------------

    /**
     * Есть ли hex-код цвета — для условного рендера цветного кружка в свитчере.
     */
    public function hasColor(): bool
    {
        return !empty($this->color_hex);
    }
}
