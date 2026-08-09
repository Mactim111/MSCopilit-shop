<?php

namespace App\Http\Controllers;

use App\Http\Requests\CatalogFilterRequest;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
// use App\Models\ProductVariant;
use App\Services\CatalogFilterService;
use Illuminate\View\View;

class CatalogController extends Controller
{
    /* 1. Каталог — все группы категорий */
    public function index(): View
    {
        $groups = Category::whereNull('parent_id')
            ->orderBy('title')
            ->get();

        return view('catalog.index', compact('groups'));
    }

    /* 2. Группа категорий — все категории группы */
    public function group(Category $group): View
    {
        $categories = $group->children()
            ->orderBy('title')
            ->get();

        return view('catalog.group', compact('group', 'categories'));
    }

    /* 3. Категория — все подкатегории */
    public function category(Category $group, Category $category): View
    {
        abort_if($category->parent_id !== $group->id, 404);

        $subcategories = $category->children()
            ->orderBy('id')
            ->get();
        
        // Проверяем, включены ли плитки брендов в подкатегории
        $showBrandTiles = $category->brand_tiles_enabled;

        return view('catalog.category', compact('group', 'category', 'subcategories','showBrandTiles'));
    }

    private function makeDynamicTitle(Category $subcategory, array $filters): string
    {
        if (empty($filters['brand'])) {
            return $subcategory->title; // "Смартфоны"
        }

        // В filters['brand'] лежат SLUG-и, а не ID
        $brandTitles = Brand::whereIn('slug', $filters['brand'])
            ->pluck('title')
            ->map(fn($t) => mb_convert_case($t, MB_CASE_TITLE)) // ДОБАВИЛИ, чтобы Заголовок подкатегории начинался с заглавной буквы - Apple, Samsung
            ->toArray();

        return $subcategory->title . ' ' . implode(', ', $brandTitles);
    }

    private function makeBreadcrumbBrandTitle(array $filters): ?string
    {
        if (empty($filters['brand'])) {
            return null;
        }

        return Brand::whereIn('slug', $filters['brand'])
            ->pluck('title')
            ->map(fn($t) => mb_strtoupper($t))
            ->implode(', ');
    }


    /* 4. ПОДКАТЕГОРИЯ — список вариантов товаров с фильтрами */
    public function subcategory(
        Category $group,
        Category $category,
        Category $subcategory,
        CatalogFilterRequest $request,     // валидирует и нормализует параметры
        CatalogFilterService $filterService // внедряем через DI
    ): View {
        // Проверка вложенности категорий
        abort_if($category->parent_id !== $group->id, 404);
        abort_if($subcategory->parent_id !== $category->id, 404);
        
        // Фильтры из запроса (?brand=apple&price_min=1000...)
        // Нормализованные фильтры из запроса.
        $filters = $request->validFilters();

        // для добавления в URL БРЕНДА (brand=apple,samsung) из ФИЛЬТРА БРЕНДОВ, если в фильтре бренда выбрано значение
        // если когда‑то останется поддержка ?brand=... — она не сломается
        $brandsParam = request()->route('brands'); // apple,samsung
        if ($brandsParam) {
            // НАША ВЕРСИЯ: читаем бренды из сегмента маршрута /brand=apple,samsung, а не из query (?brand=...)
            // $filters['brand'] = explode(',', $brandsParam);
            // НИЖЕ ЧУТЬ БЕЗОПАСНАЯ ВЕРСИЯ: фильтруем пустые значения, если вдруг кто-то передаст /brand=apple,,samsung
            $filters['brand'] = array_filter(explode(',', $brandsParam));
        }

        // ---------------------------------------------------------
        // 1. БРЕНДЫ — ВСЕ бренды ПОДкатегории, типа из "Смартфоны" — для маршрутов, хлебных крошек, тегов. НЕ меняется при выборе линейки.
        // ---------------------------------------------------------
        $brandIds = Product::where('category_id', $subcategory->id)
            ->pluck('brand_id')
            ->filter()
            ->unique();

        $brands = Brand::whereIn('id', $brandIds)
            ->orderBy('title')
            ->get();

        // Бренды для САЙДБАРА — сужаются при выборе линейки.
        // При выборе линейки показываем только бренды этих линеек.
        // В остальных случаях совпадает с $brands.
        $sidebarBrands = $filterService->getAvailableBrands(
            $subcategory,
            $filters,
            $brands  // передаём все бренды — сервис отфильтрует нужные
        );

        // Диапазон цен для слайдера — из вариантов текущей подкатегории с учётом фильтров! Ниже старый коммент на всякий случай!
        // Считаем один раз по всем вариантам, без учёта фильтров,
        // чтобы слайдер всегда показывал полный диапазон подкатегории.
        $priceRange = $filterService->getPriceRange($subcategory, $filters);
        $minPrice   = $priceRange['min'];
        $maxPrice   = $priceRange['max'];

        // Отфильтрованные варианты с пагинацией.
        // Главная сущность — ProductVariant (как в твоём исходном коде).
        $variants = $filterService->getVariants($subcategory, $filters, perPage: 20);

        // Доступные фильтры для сайдбара (свойства с used_for_filters = true,
        // у которых есть опции среди товаров данной подкатегории).
        $availableFilters = $filterService->getAvailableFilters($subcategory, $filters);

        // ---------------------------------------------------------
        // 7. Динамический заголовок страницы
        // ---------------------------------------------------------
        $title = $this->makeDynamicTitle($subcategory, $filters);
        $breadcrumbBrandTitle = $this->makeBreadcrumbBrandTitle($filters);

        return view('catalog.subcategory', compact(
            'group',
            'category',
            'subcategory',
            'variants',          // LengthAwarePaginator — совместим с твоей пагинацией
            'availableFilters',  // Collection<Property> — для сайдбара фильтров
            'minPrice',          // float — для слайдера цены
            'maxPrice',          // float — для слайдера цены
            'filters',           // array — текущие активные фильтры (для пре-чека)
            'brands',            // Collection<Brand> — для фильтра бренда - все бренды — для маршрутов и хлебных крошек
            'sidebarBrands',       // бренды для сайдбара — сужаются при линейке
            'title',             // string — динамический заголовок страницы
            'breadcrumbBrandTitle',   // string — заголовок для хлебных крошек
        ));
    }
}
