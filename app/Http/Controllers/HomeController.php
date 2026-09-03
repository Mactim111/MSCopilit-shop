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
        // $popular_variants = (clone $variantsQuery)
        //     ->orderBy('position')
        //     ->take(20)
        //     ->get();

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

        // $bannerTop = FrontendImage::where('group', 'banner-top')
        //     ->where('active', true)
        //     ->first();

        // $categoriesHitShared = view()->shared('categoriesHit'); // Получаем то, что расшарил провайдер
        // if ($categoriesHitShared) {
        //     $subcategories = $categoriesHitShared->map(function($cat) {
        //         return [
        //             'title' => $cat->title,
        //             'image' => $cat->imageUrl(),
        //             'real'  => $cat->products_count > 0,
        //             'url'   => $cat->products_count > 0
        //                 ? route('catalog.subcategory', [
        //                     $cat->parent->parent->slug,
        //                     $cat->parent->slug,
        //                     $cat->slug
        //                 ])
        //                 : null,
        //         ];
        //     });
        // }
        $popular_variants = ProductVariant::with(['product', 'labels'])
            ->inRandomOrder()
            ->limit(10)
            ->get();

        $main_banners = FrontendImage::where('group', 'main-banner-slider')
            ->where('active', true)
            ->orderBy('order')
            ->get();

        $banner_bestsellers = FrontendImage::where('group', 'banner-best-sellers')
            ->where('active', true)
            ->orderBy('order')
            ->get();

        $new_arrivals_banners = FrontendImage::where('group', 'new-arrivals-banner')
            ->where('active', true)
            ->orderBy('order')
            ->get();

        $sections = \App\Models\PageSection::where('page_name', 'home')
            ->where('active', true)
            ->orderBy('order')
            ->get();

        return view('home', compact(
            'sections',
            'popular_variants',
            'new_variants',
            'discounts',
            'groups',
            'main_banners',
            'banner_bestsellers',
            'new_arrivals_banners'
            // 'bannerTop',
            // 'subcategories',
        ));
    }
}

