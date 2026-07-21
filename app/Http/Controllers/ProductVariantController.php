<?php

namespace App\Http\Controllers;

use App\Models\ProductVariant;

/**
 * @property int $id
 * @property int $product_id
 * @property int $category_id
 * @property int $brand_id
 * @property string $title
 * @property string $slug
 */
class ProductVariantController extends Controller
{
    public function show(ProductVariant $variant)
    {
        $variant->load([
            'images',
            'labels',
            'propertyOptions.property',
            'product.brand',
            'product.category.parent.parent' // загрузим всю цепочку
        ]);

        $product = $variant->product;
        $brand = $product->brand;

        // $images = $variant->images;
        // $activeImage = $images->firstWhere('position', 1)?->url ?? $images->first()->url;
        // $activeId = $images->firstWhere('position', 1)?->id ?? $images->first()->id;

        $images = $variant->images->map(fn($img) => [
            'id' => (int)$img->id,
            'url' => $img->url,
            'position' => (int)$img->position,
        ]);

        $first = $images->firstWhere('position', 1);
        $activeImage = $first['url'] ?? $images->first()['url'];
        $activeId    = $first['id']  ?? $images->first()['id'];


        $subcategory = $product->category; // подкатегория
        $category = $subcategory->parent; // категория
        $group = $category->parent; // группа
        

        // Похожие варианты — из той же категории товара
        $relatedVariants = ProductVariant::where('id', '!=', $variant->id)
            ->whereHas('product', fn($q) =>
                $q->where('category_id', $product->category_id)
            )
            ->with(['images', 'product'])
            ->take(6)
            ->get();

        return view('variants.show', compact(
            'variant',
            'product',
            'brand',
            'variant',
            'images',
            'activeImage',
            'activeId',
            'subcategory',
            'category',
            'group',
            'relatedVariants'
        ));
    }
}
