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
        
        // Категории для узкой панели под хедером (только 2 уровень) - ПОКА В ТЕСТОВОМ РЕЖИМЕ САЙТА, где только одна категория имеет настоящие товары, 
        // а остальные ФЕЙКОВЫЕ - для наполнения КАТАЛОГА и МЕГАМЕНЮ. ПОТОМ ВЫНЕСТИ В КОНТРОЛЛЕРЫ.
        // $categoriesHit = Category::whereNotNull('parent_id') // подкатегории
        //     ->whereHas('products') // только те, где есть товары
        //     ->orderBy('id')
        //     ->take(16)
        //     ->get();

        // Категории второго уровня
        $level2 = Category::whereHas('parent', fn($q) => $q->whereNull('parent_id'))
            ->with(['children' => fn($q) => $q->withCount('products')->orderBy('title')])
            ->get();

        // Собираем подкатегории третьего уровня
        $subcategoriesLevel3 = $level2->flatMap(fn($cat) => $cat->children);

        // Сначала подкатегории с товарами
        $categoriesHitReal = $subcategoriesLevel3->filter(fn($cat) => $cat->products_count > 0);

        // Потом подкатегории без товаров
        $categoriesHitFake = $subcategoriesLevel3->filter(fn($cat) => $cat->products_count == 0);

        // Объединяем
        $categoriesHit = $categoriesHitReal->concat($categoriesHitFake)->take(16);

    // В будущем - КОГДА ВСЕ КАТЕГОРИИ будут иметь товары — популярные категории по продажам товаров из НИХ
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
