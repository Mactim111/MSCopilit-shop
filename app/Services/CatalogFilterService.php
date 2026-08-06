<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Product;
use App\Models\Property;
use App\Models\ProductVariant;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class CatalogFilterService
{
    // ------------------------------------------------------------------
    // Публичные методы
    // ------------------------------------------------------------------

    /**
     * Получить отфильтрованные ВАРИАНТЫ товаров с пагинацией.
     *
     * Главная сущность — ProductVariant, как в твоём контроллере.
     * Фильтрация по свойствам идёт через product_filter_index (по product_id),
     * ценовой фильтр и сортировка применяются прямо к таблице product_variants.
     *
     * @param  Category  $subcategory  Подкатегория (category_id в индексе)
     * @param  array     $filters      validFilters() из CatalogFilterRequest
     * @param  int       $perPage      Количество вариантов на странице
     */
    public function getVariants(
        Category $subcategory,
        array $filters,
        int $perPage = 20
    ): LengthAwarePaginator {
        // Получаем ID товаров, прошедших фильтры по свойствам.
        // Если фильтры не выбраны — берём все товары подкатегории.
        $filteredVariantIds = $this->getFilteredVariantIds($subcategory, $filters);

        // Строим запрос по вариантам — точно как в твоём subcategory().
        $query = ProductVariant::whereIn('id', $filteredVariantIds)
        ->with(['product', 'images', 'labels', 'propertyOptions.property']);

        // Ценовой фильтр применяем прямо к вариантам
        // (price живёт в product_variants, не нужен индекс).
        $priceMin = isset($filters['price_min']) ? (float) $filters['price_min'] : null;
        $priceMax = isset($filters['price_max']) ? (float) $filters['price_max'] : null;

        if ($priceMin !== null) {
            $query->where('price', '>=', $priceMin);
        }
        if ($priceMax !== null) {
            $query->where('price', '<=', $priceMax);
        }
        if (isset($filters['brand'])) {
            $query->whereHas('product.brand', fn($b) =>
                $b->whereIn('slug', $filters['brand'])
            );
        }


        // Сортировка — объединяем твои значения и наши.
        $this->applyVariantSort($query, $filters['sort'] ?? 'popular');

        return $query->paginate($perPage)->withQueryString();
    }

    /**
     * Получить доступные фильтры для сайдбара.
     *
     * Возвращает только свойства с used_for_filters = true,
     * у которых реально есть опции среди товаров данной подкатегории
     * с учётом уже выбранных фильтров (фасетный подсчёт).
     *
     * @return Collection<Property>
     */
    public function getAvailableFilters(Category $subcategory, array $filters): Collection
    {
        $filteredProductIds = $this->getFilteredProductIds($subcategory, $filters);

        return Property::forFilters()
			->where(function ($q) use ($subcategory, $filteredProductIds) {
				$q->where(function ($inner) use ($subcategory, $filteredProductIds) {
					$inner->where('type', 'toggle')
						->whereExists(function ($sub) use ($subcategory, $filteredProductIds) {
							$sub->select(DB::raw(1))
								->from('product_filter_index as pfi')
								->whereColumn('pfi.property_id', 'properties.id')
								->where('pfi.category_id', $subcategory->id)
								->whereIn('pfi.product_id', $filteredProductIds)
								->where('pfi.value_slug', 'yes');
						});
				})->orWhere(function ($inner) use ($subcategory, $filteredProductIds) {
					$inner->where('type', '!=', 'toggle')
						->whereHas('options', function ($query) use ($subcategory, $filteredProductIds) {
							$query->whereHas('variants.filterIndex', function ($q) use ($subcategory, $filteredProductIds) {
								$q->where('category_id', $subcategory->id)
								  ->whereIn('product_id', $filteredProductIds);
							});
						});
				});
			})
			->with(['options' => function ($query) use ($subcategory, $filteredProductIds) {
				$query->whereHas('variants.filterIndex', function ($q) use ($subcategory, $filteredProductIds) {
					$q->where('category_id', $subcategory->id)
					  ->whereIn('product_id', $filteredProductIds);
				})->orderByRaw('COALESCE(numeric_value, 0), value');
			}])
			->get()
			->each(function (Property $property) use ($subcategory, $filteredProductIds) {
				if ($property->isRange()) {
					$range = DB::table('product_filter_index')
						->where('category_id', $subcategory->id)
						->where('property_id', $property->id)
						->whereIn('product_id', $filteredProductIds)
						->whereNotNull('numeric_value')
						->selectRaw('MIN(numeric_value) as range_min, MAX(numeric_value) as range_max')
						->first();
					$property->range_min = (float) ($range->range_min ?? 0);
					$property->range_max = (float) ($range->range_max ?? 0);
				}
				if ($property->isToggle()) {
					$property->options->each(function ($option) use ($subcategory, $filteredProductIds) {
						$option->products_count = DB::table('product_filter_index')
							->where('category_id', $subcategory->id)
							->where('property_id', $option->property_id)
							->where('value_slug', $option->slug)
							->whereIn('product_id', $filteredProductIds)
							->distinct('product_variant_id')->count('product_variant_id');
					});
				}
				if ($property->isCheckbox() || $property->isRadio()) {
					$property->options->each(function ($option) use ($subcategory, $filteredProductIds) {
						$option->products_count = DB::table('product_filter_index')
							->where('category_id', $subcategory->id)
							->where('property_id', $option->property_id)
							->where('value_slug', $option->slug)
							->whereIn('product_id', $filteredProductIds)
							->distinct('product_variant_id')->count('product_variant_id');
					});
				}
			});
    }

    /**
     * Диапазон цен среди вариантов подкатегории.
     * Берём из product_variants напрямую — точнее чем из индекса,
     * так как в индексе price может быть устаревшей после изменения цены.
     */
    public function getPriceRange(Category $subcategory, array $filters): array
    {
        // $productIds = $subcategory->allProducts()->pluck('id');
        // товары, прошедшие ВСЕ фильтры
        $filteredProductIds = $this->getFilteredProductIds($subcategory, $filters);

        return [
            'min' => (float) (ProductVariant::whereIn('product_id', $filteredProductIds)->min('price') ?? 0),
            'max' => (float) (ProductVariant::whereIn('product_id', $filteredProductIds)->max('price') ?? 0),
        ];
    }

    // ------------------------------------------------------------------
    // Приватные методы
    // ------------------------------------------------------------------

    /**
     * ID товаров подкатегории, прошедших фильтры по свойствам.
     * Если фильтры не выбраны — возвращает все ID товаров подкатегории.
     *
     * Используется как в getVariants(), так и в getAvailableFilters()
     * для фасетного подсчёта.
     */
    private function getFilteredProductIds(Category $subcategory, array $filters): array
    {
        // Все товары подкатегории — базовый пул.
        $allProductIds = $subcategory->allProducts()->pluck('id')->all();

        $propertyFilters = $filters['f'] ?? [];

        // Если фильтры по свойствам не выбраны — возвращаем все товары.
        if (empty($propertyFilters)) {
            return $allProductIds;
        }

        // Загружаем свойства одним запросом чтобы знать их типы.
        $properties = Property::whereIn('slug', array_keys($propertyFilters))
            ->get()
            ->keyBy('slug');

        // Начинаем с полного пула и последовательно сужаем
        // через EXISTS-подзапросы к product_filter_index.
        $query = Product::whereIn('id', $allProductIds);

        if (isset($filters['brand'])) {
            $query->whereHas('brand', fn($b) =>
                $b->whereIn('slug', $filters['brand'])
            );
        }

        foreach ($propertyFilters as $propertySlug => $value) {
            $property = $properties->get($propertySlug);
            if (!$property) continue;

            match ($property->type) {
                'checkbox', 'radio' => $this->applyCheckboxFilter(
                    $query, $subcategory, $property->id, (array) $value
                ),
                // range-фильтры вынесены из foreach ($propertyFilters) НИЖЕ! в отдельный цикл по всем ключам $filters с regex f_*_min.
                // 'range' => $this->applyRangeFilter(
                //     $query, $subcategory, $property->id,
                //     $filters["f_{$propertySlug}_min"] ?? null,
                //     $filters["f_{$propertySlug}_max"] ?? null,
                // ),
                'toggle' => $this->applyToggleFilter(
                    $query, $subcategory, $property->id, $value
                ),
                default => null,
            };
        }

        // range-фильтры вынесены из foreach ($propertyFilters) ВЫШЕ! в отдельный цикл по всем ключам $filters с regex f_*_min. Теперь они точно применятся.
        // Range фильтры — отдельный проход по f_slug_min / f_slug_max.
        // Они НЕ попадают в $filters['f'] — хранятся в корне $filters.
        // Ищем все ключи вида f_*_min и подбираем соответствующее свойство.
        
        $rangeProperties = null; // загрузим лениво если понадобятся
        
        // Range фильтры в getFilteredProductIds() — через product_id, не variant_id!
        foreach ($filters as $key => $value) {
            if (!preg_match('/^f_(.+)_min$/', $key, $matches)) continue;

            $slug = $matches[1];
            $min  = (float) $value;
            $max  = isset($filters["f_{$slug}_max"]) ? (float) $filters["f_{$slug}_max"] : null;

            // Загружаем свойства одним запросом при первом range-фильтре.
            if ($rangeProperties === null) {
                $rangeProperties = Property::where('type', 'range')->get()->keyBy('slug');
            }

            $property = $rangeProperties->get($slug);
            if (!$property) continue;

            // ВАЖНО: используем! applyRangeFilter (не! applyVariantRangeFilter!) т.к. здесь базовый запрос по! таблице! products
            $this->applyRangeFilter($query, $subcategory, $property->id, $min, $max);
        }

        return $query->pluck('id')->all();
    }

    /**
     * Фильтр checkbox/radio: товар имеет хотя бы одно из выбранных значений.
     */
    private function applyCheckboxFilter(
        Builder $query,
        Category $subcategory,
        int $propertyId,
        array $slugs
    ): void {
        $slugs = array_filter($slugs);
        if (empty($slugs)) return;

        $query->whereExists(function ($sub) use ($subcategory, $propertyId, $slugs) {
            $sub->select(DB::raw(1))
                ->from('product_filter_index as pfi')
                ->whereColumn('pfi.product_id', 'products.id')
                ->where('pfi.category_id', $subcategory->id)
                ->where('pfi.property_id', $propertyId)
                ->whereIn('pfi.value_slug', $slugs);
        });
    }

    /**
     * Фильтр range: товар имеет numeric_value в заданном диапазоне.
     */
    private function applyRangeFilter(
        Builder $query,
        Category $subcategory,
        int $propertyId,
        ?float $min,
        ?float $max
    ): void {
        if ($min === null && $max === null) return;

        $query->whereExists(function ($sub) use ($subcategory, $propertyId, $min, $max) {
            $sub->select(DB::raw(1))
                ->from('product_filter_index as pfi')
                ->whereColumn('pfi.product_id', 'products.id')
                ->where('pfi.category_id', $subcategory->id)
                ->where('pfi.property_id', $propertyId)
                ->whereNotNull('pfi.numeric_value')
                ->when($min !== null, fn ($q) => $q->where('pfi.numeric_value', '>=', $min))
                ->when($max !== null, fn ($q) => $q->where('pfi.numeric_value', '<=', $max));
        });
    }

    /**
     * Фильтр toggle: показываем только товары у которых value_slug = 'yes'.
     */
    private function applyToggleFilter(
        Builder $query,
        Category $subcategory,
        int $propertyId,
        mixed $value
    ): void {
        if (!$value || $value === '0') return;

        $query->whereExists(function ($sub) use ($subcategory, $propertyId) {
            $sub->select(DB::raw(1))
                ->from('product_filter_index as pfi')
                ->whereColumn('pfi.product_id', 'products.id')
                ->where('pfi.category_id', $subcategory->id)
                ->where('pfi.property_id', $propertyId)
                ->where('pfi.value_slug', 'yes');
        });
    }

    private function getFilteredVariantIds(Category $subcategory, array $filters): array
    {
        // Стартуем с вариантов подкатегории.
        $query = ProductVariant::whereHas('product', fn($q) =>
            $q->where('category_id', $subcategory->id)
            // ->where('is_active', true)
        );

        // Фильтр по бренду.
        if (!empty($filters['brand'])) {
            $query->whereHas('product.brand', fn($q) =>
                $q->whereIn('slug', $filters['brand'])
            );
        }

        // Фильтры по свойствам (Checkbox / radio / toggle) — каждый через EXISTS на product_variant_id.
        $propertyFilters = $filters['f'] ?? [];
        if (!empty($propertyFilters)) {
            $properties = Property::whereIn('slug', array_keys($propertyFilters))
                ->get()->keyBy('slug');

            foreach ($propertyFilters as $slug => $value) {
                $property = $properties->get($slug);
                if (!$property) continue;

                match ($property->type) {
                    'checkbox', 'radio' => $this->applyVariantCheckboxFilter(
                        $query, $subcategory, $property->id, (array) $value
                    ),
                    // range-фильтры вынесены из foreach ($propertyFilters) НИЖЕ! в отдельный цикл по всем ключам $filters с regex f_*_min.
                    // 'range' => $this->applyVariantRangeFilter(
                    //     $query, $subcategory, $property->id,
                    //     $filters["f_{$slug}_min"] ?? null,
                    //     $filters["f_{$slug}_max"] ?? null,
                    // ),
                    'toggle' => $this->applyVariantToggleFilter(
                        $query, $subcategory, $property->id, $value
                    ),
                    default => null,
                };
            }
        }

        // range-фильтры вынесены из foreach ($propertyFilters) ВЫШЕ! в отдельный цикл по всем ключам $filters с regex f_*_min. Теперь они точно применятся.
        // Range фильтры — отдельный проход по f_slug_min / f_slug_max.
        // Они НЕ попадают в $filters['f'] — хранятся в корне $filters.
        // Ищем все ключи вида f_*_min и подбираем соответствующее свойство.
        $rangeProperties = null; // загрузим лениво если понадобятся
        foreach ($filters as $key => $value) {
            if (!preg_match('/^f_(.+)_min$/', $key, $matches)) continue;

            $slug = $matches[1];
            $min  = (float) $value;
            $max  = isset($filters["f_{$slug}_max"]) ? (float) $filters["f_{$slug}_max"] : null;

            // Загружаем свойства одним запросом при первом range-фильтре.
            if ($rangeProperties === null) {
                $rangeProperties = Property::where('type', 'range')
                    ->get()->keyBy('slug');
            }

            $property = $rangeProperties->get($slug);
            if (!$property) continue;

            $this->applyVariantRangeFilter($query, $subcategory, $property->id, $min, $max);
        }

        return $query->pluck('id')->all();
    }

    private function applyVariantCheckboxFilter(Builder $query, Category $subcategory, int $propertyId, array $slugs): void
    {
        $slugs = array_filter($slugs);
        if (empty($slugs)) return;

        $query->whereExists(function ($sub) use ($subcategory, $propertyId, $slugs) {
            $sub->select(DB::raw(1))
                ->from('product_filter_index as pfi')
                ->whereColumn('pfi.product_variant_id', 'product_variants.id')
                ->where('pfi.category_id', $subcategory->id)
                ->where('pfi.property_id', $propertyId)
                ->whereIn('pfi.value_slug', $slugs);
        });
    }

    private function applyVariantRangeFilter(Builder $query, Category $subcategory, int $propertyId, ?float $min, ?float $max): void
    {
        if ($min === null && $max === null) return;

        $query->whereExists(function ($sub) use ($subcategory, $propertyId, $min, $max) {
            $sub->select(DB::raw(1))
                ->from('product_filter_index as pfi')
                ->whereColumn('pfi.product_variant_id', 'product_variants.id')
                ->where('pfi.category_id', $subcategory->id)
                ->where('pfi.property_id', $propertyId)
                ->whereNotNull('pfi.numeric_value')
                ->when($min !== null, fn($q) => $q->where('pfi.numeric_value', '>=', $min))
                ->when($max !== null, fn($q) => $q->where('pfi.numeric_value', '<=', $max));
        });
    }

    private function applyVariantToggleFilter(Builder $query, Category $subcategory, int $propertyId, mixed $value): void
    {
        if (!$value || $value === '0') return;

        $query->whereExists(function ($sub) use ($subcategory, $propertyId) {
            $sub->select(DB::raw(1))
                ->from('product_filter_index as pfi')
                ->whereColumn('pfi.product_variant_id', 'product_variants.id')
                ->where('pfi.category_id', $subcategory->id)
                ->where('pfi.property_id', $propertyId)
                ->where('pfi.value_slug', 'yes');
        });
    }

    /**
     * Сортировка вариантов.
     *
     * Объединяем твои значения из sorting.blade.php:
     *   popular, price_asc, price_desc, new, discount, rating
     * и оставляем совместимость с нашими значениями из сервиса:
     *   newest (алиас для new)
     */
    private function applyVariantSort(Builder $query, string $sort): void
    {
        match ($sort) {
            'price_asc'        => $query->orderBy('price'),
            'price_desc'       => $query->orderByDesc('price'),
            'new', 'newest'    => $query->orderByDesc('created_at'),
            'discount'         => $query->orderByDesc(
                DB::raw('CASE WHEN old_price > 0 AND old_price > price
                          THEN ROUND((1 - price / old_price) * 100)
                          ELSE 0 END')
            ),
            'rating'           => $query->orderByDesc(
                DB::raw('(SELECT rating FROM products
                          WHERE products.id = product_variants.product_id)')
            ),
            // 'popular' и всё остальное — по полю position (порядок в админке)
            default            => $query->orderBy('position'),
        };
    }
}
