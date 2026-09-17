<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class ProductVariant extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'product_id',
        'title',
        'article',
        'slug',
        'excerpt',
        'description',
        'price',
        'old_price',
        'stock',
        'reserved',
        'is_active',
        'is_default',
        'position'
    ];

    protected $casts = [
        'price'      => 'decimal:2',
        'old_price'  => 'decimal:2',
        'stock'      => 'integer',
        'reserved'   => 'integer',
        'is_active'  => 'boolean',
        'is_default' => 'boolean',
        'position'   => 'integer',
    ];

    public function getRouteKeyName()
    {
        return 'slug';
    }

    /* -----------------------------------------
     |  СВЯЗИ
     |------------------------------------------*/

    // Родительский товар (модель)
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function favorites()
    {
        return $this->hasMany(Favorite::class, 'product_variant_id');
    }

    public function favoritedBy(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'favorites',
            'product_variant_id',
            'user_id'
        )->withTimestamps();
    }

    // Галерея изображений варианта
    public function images()
    {
        return $this->hasMany(ProductVariantImage::class)
            ->orderBy('position');
    }

    /**
     * Опции (значения свойств) данного варианта.
     * Через сводную таблицу product_variant_property_options.
     */
    public function propertyOptions()
    {
        return $this->belongsToMany(
            PropertyOption::class,
            'product_variant_property_options',
            'product_variant_id',
            'property_option_id'
        )->with('property')->withTimestamps();
    }
    
    public function filterIndex(): HasMany
    {
        return $this->hasMany(ProductFilterIndex::class, 'product_variant_id');
    }

    // ------------------------------------------------------------------
    // Хелперы
    // ------------------------------------------------------------------

    /**
     * Опции варианта, сгруппированные по свойству.
     * Для таблицы характеристик на странице варианта - сейчас НЕ! ИСПОЛЬЗУЕМ В ШАБЛОНЕ, НО! ВОЗМОЖНО ПРИГОДИТСЯ В БУДУЩЕМ!.
     *
     * Возвращает:
     * [
     *   ['property' => Property, 'option' => PropertyOption],
     *   ...
     * ]
     */
    public function getGroupedOptionsAttribute(): \Illuminate\Support\Collection
    {
        return $this->propertyOptions
            ->sortBy('property.position_in_filters')
            ->map(fn ($option) => [
                'property' => $option->property,
                'option'   => $option,
            ])
            ->values();
    }

    // Лейблы варианта (новинка, хит, скидка)
    public function labels()
    {
        return $this->belongsToMany(
            Label::class,
            'product_variant_labels',
            'product_variant_id',
            'label_id'
        )->withTimestamps();
    }

    // форматирование краткого описания (excerpt) для отображения на странице варианта и странице подкатегории
    public function getFormattedExcerptAttribute()
    {
        // 1. Чистим экранирование
        $text = str_replace(['\"'], ['"'], $this->excerpt);

        // 2. Разбиваем по \n (именно так, как в сидере)
        $lines = explode('\n', $text);

        // 3. Форматируем каждую строку отдельно
        $formatted = collect($lines)->map(function ($line) {
            $line = trim($line);
            if ($line === '') return '';

            if (strpos($line, ':') !== false) {
                [$label, $value] = explode(':', $line, 2);
                // вместо <strong> используем span с Tailwind классом
                return $label . ': <span class="font-semibold">' . trim($value) . '</span>';
            }

            return $line;
        })->implode('<br>'); // сохраняем переносы строк

        return $formatted;
    }

    /* -----------------------------------------
     |  ИЗОБРАЖЕНИЯ
     |------------------------------------------*/

    // Главное изображение варианта (позиция = 1)
    public function mainImage(): string
    {
        $image = $this->images()->first();

        if ($image) {
            // внешний URL
            if (Str::startsWith($image->images, ['http://', 'https://'])) {
                return $image->path;
            }

            // локальный путь
            return asset($image->path);
        }
        // fallback
        return asset('storage/assets/img/photos_will_appear_soon.jpg');
    }

    // Все изображения галереи (массив URL)
    public function gallery(): array
    {
        if ($this->images->isEmpty()) {
        return [asset('storage/assets/img/photos_will_appear_soon.jpg')];
        }

        return $this->images->map(function ($img) {
            if (Str::startsWith($img->images, ['http://', 'https://'])) {
                return $img->image;
            }
            return asset($img->images);
        })->toArray();
    }

    /* -----------------------------------------
     |  ЦЕНЫ И СКИДКИ
     |------------------------------------------*/

    public function discountPercent(): ?int
    {
        if (!$this->old_price || $this->old_price <= $this->price) {
            return null;
        }

        return round((($this->old_price - $this->price) / $this->old_price) * 100);
    }

    /**
     * Форматированные цены - СТАРАЯ и НОВАЯ - с управляемыми размерами шрифта для целой и дробной части.
     * 
     * @param int $wholeFontSize Размер шрифта целой части (в px), по умолчанию 30
     * @param int $fractionFontSize Размер шрифта дробной части (в px), по умолчанию 19
     * @return string HTML строка с ценой
     * 
     * Примеры использования в шаблоне:
     * {!! $variant->formattedPrice(30, 19) !!}  // целая 30px, дробная 19px
     * {!! $variant->formattedPrice(28, 17) !!}  // целая 28px, дробная 17px
     * {!! $variant->formattedPrice() !!}        // использует значения по умолчанию
     */
    public function formattedPrice(int $wholeFontSize = 30, int $fractionFontSize = 19): string
    {
        $price = number_format($this->price, 2, '.', ' ');
        [$whole, $fraction] = explode('.', $price);

        return "<span style=\"font-size: {$wholeFontSize}px;\">{$whole}</span><span style=\"font-size: {$fractionFontSize}px;\">.</span><span style=\"font-size: {$fractionFontSize}px;\">{$fraction}</span><i class=\"nbrb-icon\">BYN</i>";
    }

    /**
     * Форматированная старая цена с управляемыми размерами шрифта для целой и дробной части.
     * 
     * @param int $wholeFontSize Размер шрифта целой части (в px), по умолчанию 30
     * @param int $fractionFontSize Размер шрифта дробной части (в px), по умолчанию 19
     * @return string HTML строка с ценой
     * 
     * Примеры использования в шаблоне:
     * {!! $variant->formattedOldPrice(28, 17) !!}  // целая 28px, дробная 17px
     * {!! $variant->formattedOldPrice() !!}        // использует значения по умолчанию
     */
    public function formattedOldPrice(int $wholeFontSize = 30, int $fractionFontSize = 19): string
    {
        if (!$this->old_price || $this->old_price <= 0) {
            return '';
        }

        $price = number_format($this->old_price, 2, '.', ' ');
        [$whole, $fraction] = explode('.', $price);

        return "<span style=\"font-size: {$wholeFontSize}px;\">{$whole}</span><span style=\"font-size: {$fractionFontSize}px;\">.</span><span style=\"font-size: {$fractionFontSize}px;\">{$fraction}</span><i class=\"nbrb-icon\">BYN</i>";
    }

    // Ниже рефакторинг методов выше - отделили форматирование ЦЕН от самого получения цен!
    // public function formattedPrice(int $wholeFontSize = 30, int $fractionFontSize = 19): string
    // {
    //     return $this->formatPriceDisplay($this->price, $wholeFontSize, $fractionFontSize);
    // }

    // public function formattedOldPrice(int $wholeFontSize = 30, int $fractionFontSize = 19): string
    // {
    //     if (!$this->old_price || $this->old_price <= 0) return '';
    //     return $this->formatPriceDisplay($this->old_price, $wholeFontSize, $fractionFontSize);
    // }

    // Тот самый "движок", который один раз описан и работает для всех
    // private function formatPriceDisplay(float $value, int $w, int $f): string
    // {
    //     $formatted = number_format($value, 2, '.', ' ');
    //     [$whole, $fraction] = explode('.', $formatted);
    //     return "<span style=\"font-size: {$w}px;\">{$whole}</span><span style=\"font-size: {$f}px;\">.</span><span style=\"font-size: {$f}px;\">{$fraction}</span> <i class=\"nbrb-icon\">BYN</i>";
    // }

    /* -----------------------------------------
     |  УДОБНЫЕ АТРИБУТЫ
     |------------------------------------------*/
    // Проверяем доступность варианта товара для заказа
    public function scopeAvailable($query)
    {
        // Используем raw-выражение для производительности БД
        return $query->whereRaw('(stock - reserved) > 0');
    }
    
     // получить доступное для ЗАКАЗА количество нужного варианта товара на складе с учетом УЖЕ зарезервированного в других незавершенных заказах (reserved)
    public function getAvailableStockAttribute(): int
    {
        return max(0, $this->stock - $this->reserved);
    }

    // НОВЫЕ ВАРИАНТЫ МЕТОДОВ - НА БУДУЩЕЕ, ВОЗМОЖНО ПРИГОДЯТСЯ
    
    /**
     * Человекочитаемая метка варианта из его опций.
     * Например: «Чёрный / 6 ГБ / 128 ГБ».
     */
    public function getLabelAttribute(): string
    {
        return $this->propertyOptions
            ->sortBy('property.position_in_filters')
            ->pluck('value')
            ->join(' / ');
    }

    /**
     * Есть ли скидка (старая цена больше текущей).
     */
    public function hasDiscount(): bool
    {
        return $this->old_price > 0 && $this->old_price > $this->price;
    }

    /**
     * Процент скидки для отображения лейбла «-18%».
     */
    public function getDiscountPercentAttribute(): int
    {
        if (!$this->hasDiscount()) return 0;
        return (int) round((1 - $this->price / $this->old_price) * 100);
    }

    // для получения линейки товара через опции варианта (если есть опция с property.slug = 'lineup') - например для хлебных крошек на странице варианта
    public function getLineupAttribute()
    {
        // Ищем опцию, у которой свойство имеет slug = lineup
        $option = $this->propertyOptions
            ->first(fn($opt) => $opt->property->slug === 'lineup');

        return $option?->value;
    }

}
