<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

use Illuminate\Database\Eloquent\Model;

class Property extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = ['title', 'slug', 'excerpt', 'used_for_filters', 'position_in_filters', 'type', 'step', 'digits'];

    protected $casts = [
        'used_for_filters'   => 'boolean',
        'position_in_filters' => 'integer',
    ];

    // ------------------------------------------------------------------
    // Отношения
    // ------------------------------------------------------------------

    public function options()
    {
        return $this->hasMany(PropertyOption::class);
    }

    // ------------------------------------------------------------------
    // Скоупы
    // ------------------------------------------------------------------

    /**
     * Свойства, выводимые в сайдбаре фильтров категории.
     */
    public function scopeForFilters($query)
    {
        return $query->where('used_for_filters', true)
                     ->orderBy('position_in_filters');
    }

    // ------------------------------------------------------------------
    // Хелперы типа виджета
    // ------------------------------------------------------------------

    public function isCheckbox(): bool { return $this->type === 'checkbox'; }
    public function isRadio(): bool    { return $this->type === 'radio'; }
    public function isRange(): bool    { return $this->type === 'range'; }
    public function isToggle(): bool   { return $this->type === 'toggle'; }

    //TODO: check table name and fields
    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_properties', 'property_id', 'product_id')
            ->withPivot(['used_for_variant_card', 'position_in_variant_card'])
            ->withTimestamps();
    }
}
