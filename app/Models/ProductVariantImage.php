<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ProductVariantImage extends Model
{
    use HasFactory;

    protected $fillable = ['product_variant_id', 'path', 'position'];

    public function product_variant(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    // Данный метод создали для получения URL для вывода ГАЛЕРЕИ ИЗБРАЖЕНИЙ в шаблоне стрпницы варианта товара
    public function getUrlAttribute()
    {
        // Внешний URL
        if (Str::startsWith($this->path, ['http://', 'https://'])) {
            return $this->path;
        }

        // Локальный путь


        if ($this->path) {
            return asset($this->path);
        }

        // Fallback (если изображения нет)
        return asset('storage/assets/img/photos_will_appear_soon.jpg'); // или любой другой путь
    }
}
