<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Category extends Model
{
    use HasFactory;

    use SoftDeletes;

    // protected $dates = ['deleted_at']; 

    protected $fillable = ['title', 'slug', 'excerpt', 'image', 'parent_id'];

    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    // Обычные товары категории (products.category_id = categories.id)
    public function products(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Product::class);
    }

    // Товары сводной категории через pivot category_product
    public function pivotProducts()
    {
        return $this->belongsToMany(Product::class, 'category_product');
    }

    // Универсальный метод, ОБЪЕДИНЯЮЩИЙ ОБА МЕТОДА-СВЯЗЕЙ ВЫШЕ: если есть записи в pivot → сводная, иначе обычная
    public function allProducts()
    {
        if ($this->pivotProducts()->exists()) {
            return $this->pivotProducts()->with('variants')->get();
        }

        return $this->products()->with('variants')->get();
    }
    

    public function imageUrl(): string
    {
        if (!$this->image) {
            return asset('storage/assets/img/no-category.png');
        }

        if (Str::startsWith($this->image, ['http://', 'https://'])) {
            return $this->image;
        }

        return asset($this->image);
    }

    // hasManyThrough к вариантам — можно оставить, но мегаменю им не пользуется
    public function variants()
    {
        return $this->hasManyThrough(
            ProductVariant::class,
            Product::class,
            'category_id',   // FK в products
            'product_id',    // FK в variants
            'id',            // PK в categories
            'id'             // PK в products
        );
    }

    public function getRouteKeyName()
    {
        return 'slug';
    }
}
