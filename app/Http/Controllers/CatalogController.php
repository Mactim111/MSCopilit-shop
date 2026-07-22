<?php

namespace App\Http\Controllers;

use App\Http\Requests\CatalogFilterRequest;
use App\Models\Brand;
use App\Models\Category;
use App\Models\ProductVariant;
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
            ->orderBy('title')
            ->get();

        return view('catalog.category', compact('group', 'category', 'subcategories'));
    }

    /* 4. ПОДКАТЕГОРИЯ — список вариантов товаров с фильтрами */
    public function subcategory(
        Category $group,
        Category $category,
        Category $subcategory,
        CatalogFilterRequest $request,     // валидирует и нормализует параметры
        CatalogFilterService $filterService // внедряем через DI
    ): View {
        abort_if($category->parent_id !== $group->id, 404);
        abort_if($subcategory->parent_id !== $category->id, 404);
        
        // Нормализованные фильтры из запроса.
        $filters = $request->validFilters();

        // ----------------------------------------------------------------
        // Бренды — берём из РОДИТЕЛЬСКОЙ категории ($category), а не из
        // текущей подкатегории ($subcategory).
        //
        // Причина: на странице «Смартфоны Apple iPhone» все товары принадлежат
        // только Apple → $subcategory->allProducts() даёт один бренд.
        // Но пользователь должен видеть ВСЕ бренды смартфонов, чтобы мог
        // переключиться на Samsung/Xiaomi/etc. прямо из фильтра.
        //
        // $category->allProducts() для сводной подкатегории «Смартфоны, телефоны»
        // вернёт товары всех брендов через pivot-таблицу category_product.
        // ----------------------------------------------------------------

        $brands = Brand::whereIn(
            'id',
            $category->allProducts()->pluck('brand_id')->filter()->unique()
        )->orderBy('title')->get();

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

        return view('catalog.subcategory', compact(
            'group',
            'category',
            'subcategory',
            'variants',          // LengthAwarePaginator — совместим с твоей пагинацией
            'availableFilters',  // Collection<Property> — для сайдбара фильтров
            'minPrice',          // float — для слайдера цены
            'maxPrice',          // float — для слайдера цены
            'filters',           // array — текущие активные фильтры (для пре-чека)
            'brands',            // Collection<Brand> — для фильтра бренда
        ));
    }
}
