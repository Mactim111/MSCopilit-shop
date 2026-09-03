<?php

namespace App\Services;

use App\Models\ProductVariant;
use App\Models\Category;
use App\Models\FrontendImage;

class FrontendService
{
    public function getSectionData($section, $currentVariant = null)
    {
        $data = [
            'title' => $section->title,
            'tags'  => null,
            'items' => collect(),
        ];

        // 1. ПОЛУЧАЕМ ТОВАРЫ (ProductVariant)
        switch ($section->source_type) {
            case 'best_sellers':
                // Берем варианты, у которых есть лейбл "Хит" или просто по рейтингу
                $data['items'] = ProductVariant::with(['product', 'labels', 'images'])
                    ->where('is_active', true)
                    ->inRandomOrder() // Для теста
                    ->take(10)->get();
                break;

            case 'new_arrivals':
                $data['items'] = ProductVariant::with(['product', 'labels', 'images'])
                    ->where('is_active', true)
                    ->latest()
                    ->take(10)->get();
                break;

            case 'related':
                // Если мы на странице варианта, ищем другие варианты ТАКОГО ЖЕ товара
                // или товары из той же подкатегории
                if ($currentVariant) {
                    $data['items'] = ProductVariant::where('id', '!=', $currentVariant->id)
                        ->whereHas('product', function($q) use ($currentVariant) {
                            $q->where('category_id', $currentVariant->product->category_id);
                        })
                        ->with(['product', 'labels', 'images'])
                        ->take(10)->get();
                }
                break;
        }

        // 2. ПОЛУЧАЕМ ТЕГИ (если нужно)
        if ($section->show_tags && $data['items']->isNotEmpty()) {
            // Для тегов берем подкатегории (Level 3), к которым принадлежат товары из выборки выше
            $categoryIds = $data['items']->pluck('product.category_id')->unique();
            $data['tags'] = Category::whereIn('id', $categoryIds)->get();
        }

        // 3. ПОЛУЧАЕМ БАННЕРЫ (если тип секции предполагает картинки)
        if (in_array($section->type, ['double_banner', 'one_banner'])) {
            $data['items'] = FrontendImage::where('group', $section->source_value)
                ->where('active', true)
                ->orderBy('order')
                ->get();
        }

        return $data;
    }
}
