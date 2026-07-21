<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\ProductVariant;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        // Поиск по вариантам
        $search = $request->get('search');

        $variantsQuery = ProductVariant::with(['product', 'images'])
            ->when($search, function ($q) use ($search) {
                $q->where('title', 'LIKE', "%{$search}%");
            })
            ->limit(200); 


        // Популярные (пока просто сортировка по position)
        $popular_variants = (clone $variantsQuery)
            ->orderBy('position')
            ->take(20)
            ->get();

        // Новинки
        $new_variants = (clone $variantsQuery)
            ->orderByDesc('created_at')
            ->take(8)
            ->get();

        // Акции
        $discounts = (clone $variantsQuery)
            ->whereColumn('old_price', '>', 'price')
            ->orderByDesc('old_price')
            ->take(8)
            ->get();

        // Группы категорий
        $groups = Category::whereNull('parent_id')
            ->orderBy('title')
            ->get();

        return view('home', compact(
            'popular_variants',
            'new_variants',
            'discounts',
            'groups'
        ));
    }
}

