<?php

namespace App\Services;

class FrontendService
{
    public function getSectionData($section) {
        $data = [
            'title' => $section->title,
            'tags' => null,
            'items' => null
        ];

        // Логика формирования тегов
        if ($section->show_tags) {
            // Если это хиты продаж - берем топ подкатегорий
            if ($section->source_type == 'best_sellers') {
                $data['tags'] = Category::whereHas('products')...->take(8)->get();
            }
        }

        // Логика формирования товаров
        switch ($section->source_type) {
            case 'best_sellers':
                $data['items'] = ProductVariant::orderBy('sales_count', 'desc')->take(15)->get();
                break;
            case 'new_arrivals':
                $data['items'] = ProductVariant::latest()->take(15)->get();
                break;
        }

        return $data;
    }
}
