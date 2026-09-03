<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Product extends Model
{
    use HasFactory;

    use SoftDeletes;

    // protected $dates = ['deleted_at'];

    protected $fillable = [
        'category_id', 'brand_id', 'title', 'slug', 'excerpt', 'description', 'rating', 'reviews_count'
    ];

    public function getRouteKeyName()
    {
        return 'slug';
    }

    protected $casts = [
        'rating'        => 'decimal:1',
        'reviews_count' => 'integer',
    ];

    // ------------------------------------------------------------------
    // Отношения
    // ------------------------------------------------------------------

    // основная категория товара
    public function category(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    // дополнительные категории через pivot (для сводных подкатегорий, объединяющих ТОВАРЫ из нескольких подкатегорий)
    public function categories()
    {
        return $this->belongsToMany(Category::class, 'category_product');
    }

    public function brand(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function variants()
    {
        return $this->hasMany(ProductVariant::class)->orderBy('position');
    }

    public function mainVariant(): ?ProductVariant
    {
        return $this->variants()->orderBy('position')->first();
    }

    public function defaultVariant(): HasOne
    {
        return $this->hasOne(ProductVariant::class)
                    ->where('is_default', true)
                    ->withDefault(fn () => $this->variants()->first());
    }

    /**
     * СВОЙСТВА товара — оси вариативности для свитчера в карточке.
     * Через сводную таблицу product_properties.
     * Возвращает только те, у которых used_for_variant_card = true,
     * отсортированные по position_in_variant_card.
     */
    public function properties()
    {
        return $this->belongsToMany(Property::class, 'product_properties', 'product_id', 'property_id')
            ->withPivot(['used_for_variant_card', 'position_in_variant_card'])
            ->withTimestamps();
    }

    /**
     * Только свойства, выводимые в свитчере карточки варианта.
     */
    public function cardProperties(): BelongsToMany
    {
        return $this->properties()
                    ->wherePivot('used_for_variant_card', true)
                    ->orderByPivot('position_in_variant_card');
    }

    // ------------------------------------------------------------------
    // Матрица вариативности для свитчера в карточке
    // ------------------------------------------------------------------

    /**
     * Строит матрицу для свитчера:
     * для каждого свойства товара (used_for_variant_card = true)
     * возвращает список доступных опций с флагом наличия хотя бы
     * одного варианта в наличии с данной опцией.
     *
     * Структура:
     * [
     *   [
     *     'property' => Property,
     *     'options'  => [
     *       ['option' => PropertyOption, 'available' => bool],
     *       ...
     *     ],
     *   ],
     *   ...
     * ]
     */
    public function getVariantMatrixAttribute(): \Illuminate\Support\Collection
    {
        $variants = $this->variants()
            ->with('propertyOptions.property')
            ->get();

        // Маппинг: property_option_id → есть ли вариант в наличии.
        $availabilityMap = $variants
            ->flatMap(fn ($v) => $v->propertyOptions->map(fn ($opt) => [
                'option_id' => $opt->id,
                'available' => $v->stock > 0,
            ]))
            ->groupBy('option_id')
            ->map(fn ($items) => $items->contains('available', true));

        // Берём только свойства для свитчера, в правильном порядке.
        return $this->cardProperties()
            ->with(['options' => function ($query) use ($variants) {
                $usedOptionIds = $variants
                    ->flatMap(fn ($v) => $v->propertyOptions->pluck('id'))
                    ->unique();
                $query->whereIn('id', $usedOptionIds)
                    ->orderByRaw('COALESCE(numeric_value, 0), value');
            }])
            ->get()
            ->map(fn ($property) => [
                'property' => $property,
                'options'  => $property->options->map(fn ($option) => [
                    'option'    => $option,
                    'available' => $availabilityMap[$option->id] ?? false,
                ]),
            ]);
    }

    public function getReviewsLabelAttribute(): string
    {

        $count = $this->reviews_count ?? 0;

        $mod10 = $count % 10;
        $mod100 = $count % 100;

        if ($mod100 >= 11 && $mod100 <= 19) return 'отзывов';
        if ($mod10 === 1) return 'отзыв';
        if ($mod10 >= 2 && $mod10 <= 4) return 'отзыва';

        return 'отзывов';
    }

     /**
     * Форматированная цена с управляемыми размерами шрифта для целой и дробной части.
     * 
     * @param int $wholeFontSize Размер шрифта целой части (в px), по умолчанию 30
     * @param int $fractionFontSize Размер шрифта дробной части (в px), по умолчанию 19
     * @return string HTML строка с ценой
     * 
     * Примеры использования в шаблоне:
     * {!! $product->formattedPrice(30, 19) !!}  // целая 30px, дробная 19px
     * {!! $product->formattedPrice(28, 17) !!}  // целая 28px, дробная 17px
     * {!! $product->formattedPrice() !!}        // использует значения по умолчанию
     */
    public function formattedPrice(int $wholeFontSize = 30, int $fractionFontSize = 19): string
    {
        $price = number_format($this->price, 2, '.', ' ');
        [$whole, $fraction] = explode('.', $price);

        return "<span style=\"font-size: {$wholeFontSize}px;\">{$whole}</span><span style=\"font-size: {$fractionFontSize}px;\">.</span><span style=\"font-size: {$fractionFontSize}px;\">{$fraction}</span> <i class=\"nbrb-icon\">BYN</i>";
    }

    /**
     * Форматированная старая цена с управляемыми размерами шрифта для целой и дробной части.
     * 
     * @param int $wholeFontSize Размер шрифта целой части (в px), по умолчанию 30
     * @param int $fractionFontSize Размер шрифта дробной части (в px), по умолчанию 19
     * @return string HTML строка с ценой
     * 
     * Примеры использования в шаблоне:
     * {!! $product->formattedOldPrice(28, 17) !!}  // целая 28px, дробная 17px
     * {!! $product->formattedOldPrice() !!}        // использует значения по умолчанию
     */
    public function formattedOldPrice(int $wholeFontSize = 30, int $fractionFontSize = 19): string
    {
        if (!isset($this->old_price) || $this->old_price <= 0) {
            return '';
        }

        $price = number_format($this->old_price, 2, '.', ' ');
        [$whole, $fraction] = explode('.', $price);

        return "<span style=\"font-size: {$wholeFontSize}px;\">{$whole}</span><span style=\"font-size: {$fractionFontSize}px;\">.</span><span style=\"font-size: {$fractionFontSize}px;\">{$fraction}</span> <i class=\"nbrb-icon\">BYN</i>";
    }

    // ДЛЯ ЛИНЕЙКИ — возвращает значение опции свойства "линейка" текущего варианта товара, если оно есть - НА ВСЯКИЙ СЛУЧАЙ! 
    // можно в ХЛЕБНЫХ КРОШКАХ использовать, чтобы выводить ".../iPhone 14/Смартфоны" вместо просто "Смартфоны", 
    // хотя у варианта есть в модели свой аксессор getLineupAttribute(), который возвращает значение линейки
    public function getLineupAttribute()
    {
        // Берём первый вариант товара (обычно основной)
        $variant = $this->variants->first();

        if (!$variant) {
            return null;
        }

        return $variant->lineup; // используем аксессор варианта
    }

}
