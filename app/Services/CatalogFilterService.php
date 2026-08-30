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
        // Базовый пул с учётом ВСЕХ фильтров включая цену.
        $filteredProductIds = $this->getFilteredProductIds($subcategory, $filters);
        $filteredVariantIds = $this->getFilteredVariantIds($subcategory, $filters);

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

            ->with(['options' => function ($query) use ($subcategory, $filteredVariantIds, $filters) {
                // Для каждого активного свойства добавляем его "без-себя" варианты.
                // Это позволяет активному фильтру видеть все свои опции.
                $activePropertySlugs = array_keys(
                    array_filter($filters['f'] ?? [], fn($v) => !empty($v))
                );

                $extendedIds = $filteredVariantIds;

                if (!empty($activePropertySlugs)) {
                    $activeProperties = Property::whereIn('slug', $activePropertySlugs)
                        ->get()->keyBy('slug');

                    foreach ($activePropertySlugs as $slug) {
                        $filtersWithoutThis = $filters;
                        unset($filtersWithoutThis['f'][$slug]);
                        $idsWithoutThis = $this->getFilteredVariantIds($subcategory, $filtersWithoutThis);
                        $extendedIds    = array_unique(array_merge($extendedIds, $idsWithoutThis));
                    }
                }

                $query->whereHas('variants.filterIndex', function ($q) use ($subcategory, $extendedIds) {
                    $q->where('category_id', $subcategory->id)
                    ->whereIn('product_variant_id', $extendedIds);
                })->orderByRaw('COALESCE(numeric_value, 0), value');
            }])

            ->get()

            ->each(function (Property $property) use ($subcategory, $filters, $filteredProductIds, $filteredVariantIds) {

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
                    $property->options->each(function ($option) use ($subcategory, $filteredVariantIds) {
                        $option->products_count = DB::table('product_filter_index')
                            ->where('category_id', $subcategory->id)
                            ->where('property_id', $option->property_id)
                            ->where('value_slug', $option->slug)
                            ->whereIn('product_variant_id', $filteredVariantIds) // ← variant_id
                            ->distinct('product_variant_id')
                            ->count('product_variant_id');
                    });
                }

                if ($property->isCheckbox() || $property->isRadio()) {

                    // для свойств С активными значениями считаем без себя (independent)
                    $activeValues = $filters['f'][$property->slug] ?? [];

                    if (!empty($activeValues)) {
                        // Independent: показываем все опции этого свойства из пула бренд+цена.
                        // Это гарантирует что при выборе синего цвета другие цвета не скрываются.
                        $filtersOnlyBrandAndPrice = [
                            'brand'     => $filters['brand'] ?? [],
                            'price_min' => $filters['price_min'] ?? null,
                            'price_max' => $filters['price_max'] ?? null,
                        ];
                        // Добавляем фильтры по ДРУГИМ свойствам (не текущему).
                        // Это убирает рандомные линейки — lineup остаётся в фильтре.
                        $filtersWithoutSelf = $filters;
                        unset($filtersWithoutSelf['f'][$property->slug]);
                        $variantIdsWithoutSelf = $this->getFilteredVariantIds($subcategory, $filtersWithoutSelf);

                        $property->options->each(function ($option) use ($subcategory, $variantIdsWithoutSelf, $filters) {
                            $option->products_count = DB::table('product_filter_index')
                                ->where('category_id', $subcategory->id)
                                ->where('property_id', $option->property_id)
                                ->where('value_slug', $option->slug)
                                ->whereIn('product_variant_id', $variantIdsWithoutSelf)
                                ->when(!empty($filters['price_min']), fn($q) =>
                                    $q->where('price', '>=', (float) $filters['price_min'])
                                )
                                ->when(!empty($filters['price_max']), fn($q) =>
                                    $q->where('price', '<=', (float) $filters['price_max'])
                                )
                                ->distinct('product_variant_id')
                                ->count('product_variant_id');
                        });
                    }
                    else {
                        // Свойство НЕ имеет активных значений — dependent faceting.
                        // Показываем только опции из текущей выборки (с учётом всех фильтров).
                        // Скрываем опции которых нет у вариантов из текущей выборки.
                        $property->options->each(function ($option) use ($subcategory, $filteredVariantIds, $filters) {
                            $option->products_count = DB::table('product_filter_index')
                                ->where('category_id', $subcategory->id)
                                ->where('property_id', $option->property_id)
                                ->where('value_slug', $option->slug)
                                ->whereIn('product_variant_id', $filteredVariantIds)
                                ->when(!empty($filters['price_min']), fn($q) =>
                                    $q->where('price', '>=', (float) $filters['price_min'])
                                )
                                ->when(!empty($filters['price_max']), fn($q) =>
                                    $q->where('price', '<=', (float) $filters['price_max'])
                                )
                                ->distinct('product_variant_id')
                                ->count('product_variant_id');
                        });
                    }
                }
            });
    }

    /**
     * Диапазон цен среди вариантов подкатегории.
     * Берём из product_variants напрямую — точнее чем из индекса,
     * так как в индексе price может быть устаревшей после изменения цены.
     */
    // public function getPriceRange(Category $subcategory, array $filters): array
    // {
    //     // товары, прошедшие ВСЕ фильтры
    //     $filteredProductIds = $this->getFilteredProductIds($subcategory, $filters);

    //     return [
    //         'min' => (float) (ProductVariant::whereIn('product_id', $filteredProductIds)->min('price') ?? 0),
    //         'max' => (float) (ProductVariant::whereIn('product_id', $filteredProductIds)->max('price') ?? 0),
    //     ];
    // }

    public function getPriceRange(Category $subcategory, array $filters): array
    {
        // Используем variant_ids — учитываем конкретные варианты а не товары целиком.
        $filteredVariantIds = $this->getFilteredVariantIds($subcategory, $filters);

        if (empty($filteredVariantIds)) {
            return ['min' => 0, 'max' => 0];
        }

        $range = DB::table('product_filter_index')
            ->where('category_id', $subcategory->id)
            ->whereIn('product_variant_id', $filteredVariantIds) // ← variant_id вместо product_id
            ->selectRaw('MIN(price) as min_price, MAX(price) as max_price')
            ->first();

        return [
            'min' => (float) ($range->min_price ?? 0),
            'max' => (float) ($range->max_price ?? 0),
        ];
    }

    public function getAvailableBrands(
        Category $subcategory,
        array $filters,
        \Illuminate\Support\Collection $allCategoryBrands
    ): \Illuminate\Support\Collection {
        // Фильтры без бренда — для independent faceting бренда.
        $filtersWithoutBrand = $filters;
        unset($filtersWithoutBrand['brand']);

        $productIds = $this->getFilteredProductIds($subcategory, $filtersWithoutBrand);

        // Бренды из товаров прошедших ВСЕ фильтры кроме бренда (цена, свойства).
        $brandIds = Product::whereIn('id', $productIds)
            ->whereNotNull('brand_id')
            ->distinct()
            ->pluck('brand_id');

        // Фильтруем из всех брендов категории — сохраняем порядок.
        return $allCategoryBrands->whereIn('id', $brandIds)->values();
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
        // Базовый пул — товары подкатегории.
        // Для сводных подкатегорий (pivot) берём товары через category_product.
        $pivotIds = DB::table('category_product')
            ->where('category_id', $subcategory->id)
            ->pluck('product_id');

        $baseQuery = $pivotIds->isNotEmpty()
            ? Product::whereIn('id', $pivotIds)
            : Product::where('category_id', $subcategory->id);

        // --- ФИЛЬТР ПО ЦЕНЕ ---
        // ВАЖНО: применяется ДО фильтров по свойствам.
        if (!empty($filters['price_min']) || !empty($filters['price_max'])) {
            $baseQuery->whereHas('variants', function ($q) use ($filters) {
                if (!empty($filters['price_min'])) {
                    $q->where('price', '>=', (float) $filters['price_min']);
                }
                if (!empty($filters['price_max'])) {
                    $q->where('price', '<=', (float) $filters['price_max']);
                }
            });
        }

        // --- ФИЛЬТР ПО БРЕНДУ ---
        // ВАЖНО: бренд применяется СРАЗУ к базовому пулу.
        if (!empty($filters['brand'])) {
            $baseQuery->whereHas('brand', fn($b) =>
                $b->whereIn('slug', $filters['brand'])
            );
        }

        // Получаем ID всех товаров после применения цены и бренда.
        $allProductIds = $baseQuery->pluck('id')->all();

        // Фильтры по свойствам (checkbox/radio/toggle)
        $propertyFilters = $filters['f'] ?? [];

        // Проверяем наличие range-фильтров (f_slug_min / f_slug_max)

        // ниже код от Copilot c потенциальной проблемой - Паттерн /^f_.+_(min|max)$/ здесь жадный — для f_screen_size_max он не найдёт совпадение. 
        // Исправили в итоге на версию с НЕЖАДНЫМ ПАТТЕРНОМ '/^f_(.+?)_(min|max)$/' , предложенную Claude
        // $hasRangeFilters = collect(array_keys($filters))->contains(
        //     fn($k) => preg_match('/^f_.+_(min|max)$/', $k)
        // );

        // версия с НЕЖАДНЫМ ПАТТЕРНОМ от Claude, которую в итоге применяем
        $hasRangeFilters = collect(array_keys($filters))->contains(
            fn($k) => preg_match('/^f_(.+?)_(min|max)$/', $k)
        );

        // Если нет ни свойств, ни range — возвращаем базовый пул.
        if (empty($propertyFilters) && !$hasRangeFilters) {
            return $allProductIds;
        }

        // Загружаем свойства (checkbox/radio/toggle)
        $properties = Property::whereIn('slug', array_keys($propertyFilters))
            ->get()
            ->keyBy('slug');

        // Начинаем сужать пул товаров через EXISTS-подзапросы.
        $query = Product::whereIn('id', $allProductIds);

        // --- ОБРАБОТКА checkbox/radio/toggle ---
        foreach ($propertyFilters as $propertySlug => $value) {
            $property = $properties->get($propertySlug);
            if (!$property) continue;

            match ($property->type) {
                'checkbox', 'radio' => $this->applyCheckboxFilter(
                    $query, $subcategory, $property->id, (array) $value
                ),
                'toggle' => $this->applyToggleFilter(
                    $query, $subcategory, $property->id, $value
                ),
                default => null,
            };
        }

        // --- ФИНАЛЬНАЯ, ПРАВИЛЬНАЯ ОБРАБОТКА RANGE-ФИЛЬТРОВ ---
        // ВАЖНО: мы НЕ обрабатываем range внутри foreach(propertyFilters),
        // потому что range-фильтры НЕ лежат в $filters['f'], а в корне $filters.

        // 1) Собираем все min/max в структуру:
        //    ['screen_size' => ['min' => 6.67, 'max' => 6.77], ...]
        $rangeFilters = [];

        foreach ($filters as $key => $value) {
            // Ищем f_slug_min или f_slug_max
            if (!preg_match('/^f_(.+?)_(min|max)$/', $key, $matches)) {
                continue;
            }

            $slug = $matches[1];   // screen_size
            $type = $matches[2];   // min или max

            if (!isset($rangeFilters[$slug])) {
                $rangeFilters[$slug] = ['min' => null, 'max' => null];
            }

            // ВАЖНО: теперь min и max НЕ путаются!
            $rangeFilters[$slug][$type] = (float) $value;
        }

        // 2) Применяем диапазоны

        // ниже код предложил Copilot
        // $rangeProperties = null;
        // foreach ($rangeFilters as $slug => $bounds) {
        //     $min = $bounds['min'];
        //     $max = $bounds['max'];

        //     // Загружаем свойства типа range
        //     if ($rangeProperties === null) {
        //         $rangeProperties = Property::where('type', 'range')->get()->keyBy('slug');
        //     }

        //     $property = $rangeProperties->get($slug);
        //     if (!$property) continue;

        //     // ВАЖНО: здесь applyRangeFilter работает по product_id
        //     $this->applyRangeFilter($query, $subcategory, $property->id, $min, $max);
        // }

        // Применили код от Claude, где он предложил вынести загрузку $rangeProperties перед! циклом
        // То есть убираем $rangeProperties = null и проверку === null внутри цикла, заменяем на одну проверку !empty($rangeFilters) снаружи. 
        // Запрос к БД выполняется один раз до цикла, а не лениво внутри.

        if (!empty($rangeFilters)) {
            $rangeProperties = Property::where('type', 'range')
                ->get()->keyBy('slug');

            foreach ($rangeFilters as $slug => $bounds) {
                $property = $rangeProperties->get($slug);
                if (!$property) continue;

                $this->applyRangeFilter(
                    $query, $subcategory, $property->id,
                    $bounds['min'], $bounds['max']
                );
            }
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
            ->where('is_active', true)
        );

        // --- ФИЛЬТР ПО БРЕНДУ ---
        if (!empty($filters['brand'])) {
            $query->whereHas('product.brand', fn($q) =>
                $q->whereIn('slug', $filters['brand'])
            );
        }

        // --- ФИЛЬТРЫ checkbox/radio/toggle ---
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
                    'toggle' => $this->applyVariantToggleFilter(
                        $query, $subcategory, $property->id, $value
                    ),
                    default => null,
                };
            }
        }

        // --- ФИНАЛЬНАЯ ОБРАБОТКА RANGE-ФИЛЬТРОВ ---
        // Собираем min/max в структуру
        $rangeFilters = [];

        foreach ($filters as $key => $value) {
            if (!preg_match('/^f_(.+?)_(min|max)$/', $key, $matches)) {
                continue;
            }

            $slug = $matches[1];
            $type = $matches[2];

            if (!isset($rangeFilters[$slug])) {
                $rangeFilters[$slug] = ['min' => null, 'max' => null];
            }

            $rangeFilters[$slug][$type] = (float) $value;
        }

        // Применяем диапазоны

        // !!!ниже код, предложенный Copilot!!! где загрузка $rangeProperties внутри цикла foreach - Это правильно — lazy loading, один запрос на все range-свойства. 
        // Но можно вынести загрузку $rangeProperties перед циклом для чистоты, как предложил Claude - что мы потом и сделали
        // $rangeProperties = null;

        // foreach ($rangeFilters as $slug => $bounds) {
        //     $min = $bounds['min'];
        //     $max = $bounds['max'];

        //     if ($rangeProperties === null) {
        //         $rangeProperties = Property::where('type', 'range')
        //             ->get()->keyBy('slug');
        //     }

        //     $property = $rangeProperties->get($slug);
        //     if (!$property) continue;

        //     // ВАЖНО: здесь applyVariantRangeFilter работает по variant_id
        //     $this->applyVariantRangeFilter($query, $subcategory, $property->id, $min, $max);
        // }

        // Мы же использовали код от Claude, где он предложил вынести загрузку $rangeProperties перед циклом для чистоты
        // То есть убираем $rangeProperties = null и проверку === null внутри цикла, заменяем на одну проверку !empty($rangeFilters) снаружи. 
        // Запрос к БД выполняется один раз до цикла, а не лениво внутри.
        if (!empty($rangeFilters)) {
            $rangeProperties = Property::where('type', 'range')
                ->get()->keyBy('slug');

            foreach ($rangeFilters as $slug => $bounds) {
                $property = $rangeProperties->get($slug);
                if (!$property) continue;

                $this->applyVariantRangeFilter(
                    $query, $subcategory, $property->id,
                    $bounds['min'], $bounds['max']
                );
            }
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
