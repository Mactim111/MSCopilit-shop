<?php

namespace App\Providers;

use App\Models\Category;
use Illuminate\Support\ServiceProvider;
use App\Models\OrderItem;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Blade;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Blade::component('partials.breadcrumbs', 'breadcrumbs');
        
        // Категории для узкой панели под хедером (только 2 уровень)
        $categoriesHit = Category::whereNotNull('parent_id')
            ->whereHas('parent', fn($q) => $q->whereNull('parent_id'))
            ->orderBy('id')
            ->take(10)
            ->get();

    // В будущем — популярные категории по продажам
    // $categoriesHit = Category::select('categories.*')
    //     ->join('products', 'products.category_id', '=', 'categories.id')
    //     ->join('order_items', 'order_items.product_id', '=', 'products.id')
    //     ->whereNotNull('categories.parent_id')
    //     ->whereHas('parent', fn($q) => $q->whereNull('parent_id'))
    //     ->selectRaw('COUNT(order_items.id) as sold_count')
    //     ->groupBy('categories.id')
    //     ->orderByDesc('sold_count')
    //     ->take(10)
    //     ->get();  

        View::share('categoriesHit', $categoriesHit);

        View::composer('*', function ($view) {
            $actionsCategory = Category::where('slug', 'sales')->first();

            $categoryGroups = Category::with(['children.children'])
                ->whereNull('parent_id')
                ->when($actionsCategory, fn($q) => $q->where('id', '!=', $actionsCategory->id))
                ->orderBy('title')
                ->get();

            $view->with(compact('actionsCategory', 'categoryGroups'));
        });
    }
}
