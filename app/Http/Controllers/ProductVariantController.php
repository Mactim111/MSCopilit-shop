<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\ProductVariant;
use Illuminate\View\View;

class ProductVariantController extends Controller
{
    public function show(ProductVariant $variant): View
    {
        // ----------------------------------------------------------------
        // Загружаем всё что нужно одним запросом.
        //
        // Для свитчера добавлены:
        //   product.cardProperties.options  — свойства товара с used_for_variant_card=true
        //                                     и их опции (для построения матрицы)
        //   product.variants.propertyOptions.property — все варианты товара с опциями
        //                                     (нужно getVariantMatrixAttribute())
        //
        // Остальные relations — твои оригинальные.
        // ----------------------------------------------------------------
        $variant->load([
            'images',
            'labels',
            'propertyOptions.property',
            'product.brand',
            'product.category.parent.parent',

            // Для свитчера:
            'product.cardProperties.options',
            'product.variants.propertyOptions.property',
        ]);

        $product = $variant->product;
        $brand   = $product->brand;

        // ----------------------------------------------------------------
        // Матрица вариативности для свитчера.
        //
        // $variantMatrix — Collection, каждый элемент:
        // [
        //   'property' => Property (title, id),
        //   'options'  => [
        //     ['option' => PropertyOption, 'available' => bool],
        //     ...
        //   ],
        // ]
        //
        // Геттер getVariantMatrixAttribute() определён в модели Product.
        // Он использует уже загруженные relations — дополнительных запросов нет.
        // ----------------------------------------------------------------
        $variantMatrix = $product->variant_matrix;

        // ----------------------------------------------------------------
        // Твой оригинальный код (без изменений)
        // ----------------------------------------------------------------
        $images = $variant->images->map(fn($img) => [
            'id'       => (int) $img->id,
            'url'      => $img->url,
            'position' => (int) $img->position,
        ]);

        $first       = $images->firstWhere('position', 1);
        $activeImage = $first['url'] ?? $images->first()['url'] ?? null;
        $activeId    = $first['id']  ?? $images->first()['id']  ?? null;

        $subcategory = $product->category;
        $category    = $subcategory?->parent;
        $group       = $category?->parent;

        $relatedVariants = ProductVariant::where('id', '!=', $variant->id)
            ->whereHas('product', fn($q) =>
                $q->where('category_id', $product->category_id)
            )
            ->with(['images', 'product'])
            ->take(6)
            ->get();

        // Переменные для слайдеров с карточками вариантов товаров типа Хиты продаж
        $popular_variants = ProductVariant::with(['product', 'labels'])
            ->inRandomOrder()
            ->limit(10)
            ->get();

        // ---------------------------------------------------------
        // ЗАГРУЗКА ДИНАМИЧЕСКИХ СЕКЦИЙ (Похожие, Вместе, Ранее смотрели)
        // ---------------------------------------------------------
        $sections = \App\Models\PageSection::where('page_name', 'product_show')
            ->where('active', true)
            ->orderBy('order')
            ->get();

        // Добавляем! ниже 'sections' в compact

        return view('variants.show', compact(
            'variant',
            'product',
            'brand',
            'images',
            'activeImage',
            'activeId',
            'subcategory',
            'category',
            'group',
            'relatedVariants',
            'popular_variants',
            'variantMatrix',   // ← добавлено для свитчера
            'sections',        // ← добавлено для динамических секций
        ));
    }
}
