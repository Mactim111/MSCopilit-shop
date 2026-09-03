<?php

namespace App\Services;

use App\Models\Category;

class FrontendService
{
    /**
     * Подготавливает данные для конкретной секции (виджета) на странице.
     */
    public function getSectionData($section, $currentVariant = null)
    {
        $data = [
            'title' => $section->title,
            'tags'  => null,
            'items' => collect(),
        ];

        // 1. Логика получения КОНТЕНТА (товары, картинки или ранее просмотренные)
        
        // Источник: Изображения из таблицы frontend_images
        if ($section->source_type === 'images') {
            $data['items'] = \App\Models\FrontendImage::where('group', $section->source_value)
                ->where('active', true)
                ->orderBy('order')
                ->get();
        } 
        
        // Источник: Хиты продаж
        elseif ($section->source_type === 'best_sellers') {
            $data['items'] = \App\Models\ProductVariant::with(['product', 'labels', 'images'])
                ->where('is_active', true)
                ->inRandomOrder()
                ->take(10)
                ->get();
        } 
        
        // Источник: Новинки
        elseif ($section->source_type === 'new_arrivals') {
            $data['items'] = \App\Models\ProductVariant::with(['product', 'labels', 'images'])
                ->where('is_active', true)
                ->latest()
                ->take(10)
                ->get();
        }

        // Источник: Похожие / С этим покупают (только если есть $currentVariant)
        elseif ($section->source_type === 'related' && $currentVariant) {
            $data['items'] = \App\Models\ProductVariant::where('id', '!=', $currentVariant->id)
                ->whereHas('product', function($q) use ($currentVariant) {
                    // Ищем товары в той же подкатегории
                    $q->where('category_id', $currentVariant->product->category_id);
                })
                ->with(['product', 'labels', 'images'])
                ->where('is_active', true)
                ->take(10)
                ->get();
        }

        // Специальный тип: Ранее вы смотрели (логика отображения мелких карточек)
        if ($section->type === 'recently_viewed') {
            // Пока для теста берем рандомные варианты, позже заменим на логику из Cookies/Session
            $data['items'] = \App\Models\ProductVariant::with(['product', 'images'])
                ->where('is_active', true)
                ->inRandomOrder()
                ->take(10)
                ->get();
        }

        // 2. Логика ТЕГОВ (берем твои категории из View::share или БД)
        // Они будут отображаться над слайдером, если в БД у секции show_tags = true
        if ($section->show_tags) {
            $data['tags'] = view()->shared('categoriesHit');
        }

        return $data;
    }
}
