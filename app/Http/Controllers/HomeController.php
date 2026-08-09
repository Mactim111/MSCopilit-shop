<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\FrontendImage;
use App\Models\ProductVariant;
use Illuminate\Http\Request;

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

        $banners = FrontendImage::where('group', 'banner-slider')
            ->where('active', true)
            ->orderBy('order')
            ->get();

        $bannerTop = FrontendImage::where('group', 'banner-top')
            ->where('active', true)
            ->first();


        return view('home', compact(
            'popular_variants',
            'new_variants',
            'discounts',
            'groups',
            'banners',
            'bannerTop'
        ));
    }
}

