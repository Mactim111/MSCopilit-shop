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
use Illuminate\Support\Facades\Log;

class CatalogFilterService
{
    // ------------------------------------------------------------------
    // Публичные методы
    // ------------------------------------------------------------------

    /**
     * Получить отфильтрованные ВАРИАНТЫ товаров с пагинацией.
     *
     * Главная сущность — ProductVariant, как в твоём контроллере.
     * Пул ID вариантов строится в getFilteredVariantIds(): он учитывает наличие
     * (stock - reserved > 0), is_active, фильтр бренда и фильтры свойств
     * (checkbox / radio / toggle / range) через product_filter_index
     * (по product_variant_id). Ценовой фильтр в пул не входит и применяется
     * здесь, прямо к таблице product_variants.
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
        // ID вариантов, прошедших фильтр бренда и фильтры свойств.
        // Если фильтры не выбраны — все доступные варианты подкатегории.
        $filteredVariantIds = $this->getFilteredVariantIds($subcategory, $filters);

        // Строим запрос по вариантам — точно как в твоём subcategory().
        $query = ProductVariant::whereIn('id', $filteredVariantIds)
            ->with(['product', 'images', 'labels', 'propertyOptions.property'])
            ->withFavoriteState();

        // Ценовой фильтр применяем прямо к вариантам
        // (price живёт в product_variants, не нужен индекс).
        // Цена намеренно не входит в getFilteredVariantIds(): иначе слайдер цены
        // и счётчики фильтров схлопывались бы до выбранного диапазона.
        $priceMin = isset($filters['price_min']) ? (float) $filters['price_min'] : null;
        $priceMax = isset($filters['price_max']) ? (float) $filters['price_max'] : null;

        if ($priceMin !== null) {
            $query->where('price', '>=', $priceMin);
        }
        if ($priceMax !== null) {
            $query->where('price', '<=', $priceMax);
        }

        // Фильтр по бренду здесь не нужен: он уже применён внутри
        // getFilteredVariantIds(), поэтому повторный whereHas('product.brand', ...)
        // только добавлял лишний подзапрос.

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
        $filteredVariantIds = $this->getFilteredVariantIds($subcategory, $filters);

        return Property::forFilters()
            ->where(function ($q) use ($subcategory, $filteredVariantIds, $filters) {
                // toggle: блок нужен, только если среди доступных вариантов пула есть «yes»
                // (и они проходят ценовой фильтр).
                $q->where(function ($inner) use ($subcategory, $filteredVariantIds, $filters) {
                    $inner->where('type', 'toggle')
                        ->whereExists(function ($sub) use ($subcategory, $filteredVariantIds, $filters) {
                            $sub->select(DB::raw(1))
                                ->from('product_filter_index as pfi')
                                ->whereColumn('pfi.property_id', 'properties.id')
                                ->where('pfi.category_id', $subcategory->id)
                                ->whereIn('pfi.product_variant_id', $filteredVariantIds)
                                ->where('pfi.value_slug', 'yes');
                            $this->wherePriceInIndex($sub, $filters, 'pfi.price');
                        });
                })->orWhere(function ($inner) use ($subcategory, $filteredVariantIds, $filters) {
                    // checkbox / radio / range: хотя бы одна опция есть у доступного варианта пула.
                    $inner->where('type', '!=', 'toggle')
                        ->whereHas('options', function ($query) use ($subcategory, $filteredVariantIds, $filters) {
                            $query->whereHas('variants.filterIndex', function ($q) use ($subcategory, $filteredVariantIds, $filters) {
                                $q->where('category_id', $subcategory->id)
                                ->whereIn('product_variant_id', $filteredVariantIds);
                                $this->wherePriceInIndex($q, $filters);
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

            ->each(function (Property $property) use ($subcategory, $filters, $filteredVariantIds) {

                if ($property->isRange()) {
                    $rangeQuery = DB::table('product_filter_index')
                        ->where('category_id', $subcategory->id)
                        ->where('property_id', $property->id)
                        ->whereIn('product_variant_id', $filteredVariantIds)   // было product_id / $filteredProductIds
                        ->whereNotNull('numeric_value');

                    $this->wherePriceInIndex($rangeQuery, $filters);

                    $range = $rangeQuery
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

        // старая версия метода - в новой версии ниже мы будем брать цены из product_variants, а не из индексной таблицы 
        // product_filter_index, чтобы не было проблем с устаревшими ценами в индексе
        // $range = DB::table('product_filter_index')
        //     ->where('category_id', $subcategory->id)
        //     ->whereIn('product_variant_id', $filteredVariantIds) // ← variant_id вместо product_id
        //     ->selectRaw('MIN(price) as min_price, MAX(price) as max_price')
        //     ->first();

        // Теперь мы не зависим от актуальности price в индексе. Кроме того, в индексе есть строки только у вариантов с хотя бы одной опцией. 
        // Вариант без опций в диапазон не попадёт, хотя в списке карточек он будет.
        $range = ProductVariant::whereIn('id', $filteredVariantIds)
        ->selectRaw('MIN(price) as min_price, MAX(price) as max_price')
        ->first();

        return [
            'min' => (float) ($range->min_price ?? 0),
            'max' => (float) ($range->max_price ?? 0),
        ];
    }

    // НИЖЕ Старый метод -работает на уровне товара через getFilteredProductIds(), и это даёт две проблемы:
    // Наличие. getFilteredProductIds() про available() не знает. Бренд может показаться из-за товара, у которого все варианты в резерве.
    // Комбинации фильтров. На уровне товара условия проверяются по отдельности. Например, выбраны «чёрный» и «8 ГБ». Товар проходит, если у него есть чёрный вариант и
    // есть вариант на 8 ГБ, хотя одного варианта «чёрный и 8 ГБ» может не быть. Бренд покажется, а клик по нему даст «Ничего не найдено». Та же логика действует для цены: 
    // проверяется, есть ли у товара какой-то вариант в диапазоне, а не тот, что проходит остальные фильтры.

    // public function getAvailableBrands(
    //     Category $subcategory,
    //     array $filters,
    //     \Illuminate\Support\Collection $allCategoryBrands
    // ): \Illuminate\Support\Collection {
        // Фильтры без бренда — для independent faceting бренда.
        // $filtersWithoutBrand = $filters;
        // unset($filtersWithoutBrand['brand']);

        // $productIds = $this->getFilteredProductIds($subcategory, $filtersWithoutBrand);

        // Бренды из товаров прошедших ВСЕ фильтры кроме бренда (цена, свойства).
        // $brandIds = Product::whereIn('id', $productIds)
        //     ->whereNotNull('brand_id')
        //     ->distinct()
        //     ->pluck('brand_id');

        // Фильтруем из всех брендов категории — сохраняем порядок.
    //     return $allCategoryBrands->whereIn('id', $brandIds)->values();
    // }

    // Новая версия метода - считать бренды от пула вариантов, как и всё остальное.
    // Два запроса вместо JOIN выбраны намеренно: так нет неоднозначных колонок вроде id и price, с которыми вы уже сталкивались.
    public function getAvailableBrands(
        Category $subcategory,
        array $filters,
        \Illuminate\Support\Collection $allCategoryBrands
    ): \Illuminate\Support\Collection {
        // Все фильтры, кроме бренда (independent faceting для самого бренда).
        $filtersWithoutBrand = $filters;
        unset($filtersWithoutBrand['brand']);

        // Доступные варианты, прошедшие свойства (available() и is_activeуже внутри).
        $variantIds = $this->getFilteredVariantIds($subcategory, $filtersWithoutBrand);

        if (empty($variantIds)) {
            return collect();
        }

        // Цену накладываем на сами варианты: getFilteredVariantIds() её не применяет.
        $productIds = ProductVariant::whereIn('id', $variantIds)
            ->when(!empty($filters['price_min']), fn($q) => $q->where('price', '>=', (float) $filters['price_min']))
            ->when(!empty($filters['price_max']), fn($q) => $q->where('price', '<=', (float) $filters['price_max']))
            ->distinct()
            ->pluck('product_id');

        $brandIds = Product::whereIn('id', $productIds)
            ->whereNotNull('brand_id')
            ->distinct()
            ->pluck('brand_id');

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

    private function getFilteredVariantIds(Category $subcategory, array $filters): array
    {
        // Стартуем с вариантов подкатегории.
        // available() оставляет только варианты, доступные для заказа: (stock - reserved) > 0.
        // where('is_active', true) относится к самому варианту (product_variants.is_active).
        // Подзапрос к products проверяет подкатегорию и (если выбран) бренд.
        // Товар-родитель — техническая сущность: его активность не проверяем,
        // а мягкое удаление отсекается через SoftDeletes автоматически.
        $query = ProductVariant::available()
            ->where('is_active', true)
            ->whereHas('product', function ($q) use ($subcategory, $filters) {
                $q->where('category_id', $subcategory->id)
                // при отсутствии поля `is_active` в таблице `products` - если не нужен функционал АКТИВНОСТИ ТОВАРА - строку ниже можно закоммнтить: активность задаёт сам вариант.
                ->where('products.is_active', true)
                // Фильтр по бренду теперь внутри того же подзапроса к products,
                // поэтому отдельный блок "ФИЛЬТР ПО БРЕНДУ" ниже удалён.
                ->when(!empty($filters['brand']), fn($q) =>
                    $q->whereHas('brand', fn($b) => $b->whereIn('slug', $filters['brand']))
                );
            });

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
        // Вынесли загрузку $rangeProperties перед циклом для чистоты
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

    /**
    * В коде методов НИЖЕ были применены некоторые Необязательные! улучшения:
    * array_filter($slugs) без колбэка удаляет все «ложные» значения, в том числе строку "0". Для ваших slug это не проблема, но надёжнее фильтровать только пустые.
    * Границы range. Колонка в индексе DECIMAL(10,2), значит, значения точнее двух знаков там всё равно не хранятся. Округление параметров до двух знаков делает сравнение 
    * согласованным со схемой.
    * Перепутанные границы. Если вручную в URL указать min больше max, выдача окажется пустой. Можно поменять их местами.
    */

    /**
     * Фильтр checkbox/radio: вариант проходит, если у него есть ХОТЯ БЫ ОДНО из выбранных
     * значений данного свойства (ИЛИ внутри свойства; между свойствами работает И).
     */
    private function applyVariantCheckboxFilter(Builder $query, Category $subcategory, int $propertyId, array $slugs): void
    {
        // ИЗМЕНЕНО: раньше array_filter($slugs) без колбэка выбрасывал и строку "0".
        // Теперь отбрасываем только null и пустые строки.
        $slugs = array_values(array_filter($slugs, fn($s) => $s !== null && $s !== ''));
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

    /**
     * Фильтр range: у варианта numeric_value этого свойства лежит в [min; max].
     * Любая из границ может быть null (задана только одна сторона).
     */
    private function applyVariantRangeFilter(Builder $query, Category $subcategory, int $propertyId, ?float $min, ?float $max): void
    {
        if ($min === null && $max === null) return;

        // ИЗМЕНЕНО: если границы перепутаны (min > max, например из ручного URL),
        // меняем их местами вместо пустой выдачи.
        if ($min !== null && $max !== null && $min > $max) {
            [$min, $max] = [$max, $min];
        }

        // ИЗМЕНЕНО: numeric_value в индексе имеет тип DECIMAL(10,2),
        // поэтому сравниваем с границами, округлёнными до 2 знаков.
        $min = $min !== null ? round($min, 2) : null;
        $max = $max !== null ? round($max, 2) : null;

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

    /**
     * Фильтр toggle: применяется только при значении 'yes' (переключатель включён).
     * Любое другое значение (например ?f[nfc][]=no) игнорируется.
     */
    private function applyVariantToggleFilter(Builder $query, Category $subcategory, int $propertyId, mixed $value): void
    {
        // Строгая проверка: $value приходит массивом, поэтому !$value здесь не срабатывало бы.
        // (Старые закомментированные варианты условия удалены.)
        if (!in_array('yes', (array) $value, true)) return;

        $query->whereExists(function ($sub) use ($subcategory, $propertyId) {
            $sub->select(DB::raw(1))
                ->from('product_filter_index as pfi')
                ->whereColumn('pfi.product_variant_id', 'product_variants.id')
                ->where('pfi.category_id', $subcategory->id)
                ->where('pfi.property_id', $propertyId)
                ->where('pfi.value_slug', 'yes');
        });
    }

    // Старый код методов applyVariantCheckboxFilter, applyVariantRangeFilter, applyVariantToggleFilter закомментирован ниже для справки. Новые версии этих методов вынесены выше 
    // в коде, с улучшениями.
    // private function applyVariantCheckboxFilter(Builder $query, Category $subcategory, int $propertyId, array $slugs): void
    // {
    //     $slugs = array_filter($slugs);
    //     if (empty($slugs)) return;

    //     $query->whereExists(function ($sub) use ($subcategory, $propertyId, $slugs) {
    //         $sub->select(DB::raw(1))
    //             ->from('product_filter_index as pfi')
    //             ->whereColumn('pfi.product_variant_id', 'product_variants.id')
    //             ->where('pfi.category_id', $subcategory->id)
    //             ->where('pfi.property_id', $propertyId)
    //             ->whereIn('pfi.value_slug', $slugs);
    //     });
    // }

    // private function applyVariantRangeFilter(Builder $query, Category $subcategory, int $propertyId, ?float $min, ?float $max): void
    // {
    //     if ($min === null && $max === null) return;

    //     $query->whereExists(function ($sub) use ($subcategory, $propertyId, $min, $max) {
    //         $sub->select(DB::raw(1))
    //             ->from('product_filter_index as pfi')
    //             ->whereColumn('pfi.product_variant_id', 'product_variants.id')
    //             ->where('pfi.category_id', $subcategory->id)
    //             ->where('pfi.property_id', $propertyId)
    //             ->whereNotNull('pfi.numeric_value')
    //             ->when($min !== null, fn($q) => $q->where('pfi.numeric_value', '>=', $min))
    //             ->when($max !== null, fn($q) => $q->where('pfi.numeric_value', '<=', $max));
    //     });
    // }

    // private function applyVariantToggleFilter(Builder $query, Category $subcategory, int $propertyId, mixed $value): void
    // {
    //     // заменили этот вариант условия на код ниже, т.к. текущее !$value || $value === '0' для массива не срабатывает никогда, и ?f[nfc][]=no применит условие yes.
    //     // if (!$value || $value === '0') return;
    //     // НИЖЕ еще один вариант УСЛОВИЯ if ВЫШЕ
    //     if (!in_array('yes', (array) $value, true)) return;

    //     $query->whereExists(function ($sub) use ($subcategory, $propertyId) {
    //         $sub->select(DB::raw(1))
    //             ->from('product_filter_index as pfi')
    //             ->whereColumn('pfi.product_variant_id', 'product_variants.id')
    //             ->where('pfi.category_id', $subcategory->id)
    //             ->where('pfi.property_id', $propertyId)
    //             ->where('pfi.value_slug', 'yes');
    //     });
    // }

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


    /**
     * Формирует набор тегов для блока быстрых фильтров над списком товаров.
     * Возвращает Collection объектов с полями:
     *   label  — текст плитки («Apple», «128 ГБ», «Недорогие»)
     *   url    — URL клика (применяет соответствующий фильтр)
     *   type   — 'brand' | 'lineup' | 'property' | 'sort' | 'price'
     */
    public function getSubcategoryTags(
        Category $subcategory,
        array $routeParams,
        array $filters = [],  // текущие активные фильтры
        int $limit = 30       // максимальное количество плиток в блоке
    ): \Illuminate\Support\Collection {

        // НОВОЕ: оставлены по одной переменной вместо дублей ($activeBrand / $activeLineup).
        $activeBrandSlug  = $filters['brand'][0] ?? null;
        $activeLineupSlug = $filters['f']['lineup'][0] ?? null;
        $activeSortValue  = $filters['sort'] ?? null;

        $tags    = collect();
        $baseUrl = route('catalog.subcategory', $routeParams);

        // НОВОЕ: единый пул доступных вариантов подкатегории.
        // getFilteredVariantIds() уже исключает варианты с (stock - reserved) < 1,
        // неактивные и удалённые, поэтому правила наличия живут в одном месте.
        $poolIds = $this->getFilteredVariantIds($subcategory, []);
        if (empty($poolIds)) {
            return collect();
        }

        // ── Если выбран бренд, но не линейка — показываем линейки этого бренда ──
        if ($activeBrandSlug && !$activeLineupSlug) {
            $brandId = DB::table('brands')->where('slug', $activeBrandSlug)->value('id');

            if ($brandId) {
                $lineupProperty = DB::table('properties')->where('slug', 'lineup')->value('id');

                if ($lineupProperty) {
                    $lineups = DB::table('product_filter_index')
                        ->where('product_filter_index.category_id', $subcategory->id)
                        ->whereIn('product_filter_index.product_variant_id', $poolIds) // НОВОЕ
                        ->where('product_filter_index.property_id', $lineupProperty)
                        ->join('products', 'products.id', '=', 'product_filter_index.product_id')
                        ->where('products.brand_id', $brandId)
                        ->join('property_options', function ($join) use ($lineupProperty) {
                            $join->on('property_options.slug', '=', 'product_filter_index.value_slug')
                                ->where('property_options.property_id', '=', $lineupProperty);
                        })
                        ->select('property_options.value', 'product_filter_index.value_slug')
                        ->selectRaw('COUNT(DISTINCT product_filter_index.product_variant_id) as cnt')
                        ->whereNull('products.deleted_at')
                        ->groupBy('property_options.value', 'product_filter_index.value_slug')
                        ->orderByDesc('cnt')
                        ->limit($limit)
                        ->get();

                    foreach ($lineups as $lineup) {
                        // URL: сбрасываем всё, ставим только бренд + линейку.
                        $tags->push([
                            'label'     => $lineup->value,
                            'url'       => route('catalog.subcategory.brand', [...$routeParams, $activeBrandSlug])
                                            . '?f[lineup][]=' . urlencode($lineup->value_slug),
                            'type'      => 'lineup',
                            // НОВОЕ: в этой ветке линейка по условию не выбрана, значит всегда false.
                            'active'    => false,
                            'reset_url' => route('catalog.subcategory.brand', [...$routeParams, $activeBrandSlug]),
                        ]);
                    }
                }
            }

            return $tags->take($limit);
        }

        // ── Сортировка ────────────────────────────────────────────────
        $tags->push([
            'label'  => 'Недорогие',
            'url'    => $baseUrl . '?sort=price_asc',
            'type'   => 'sort',
            'active' => $activeSortValue === 'price_asc',
        ]);

        // ── Бренды ────────────────────────────────────────────────────
        $brandCounts = DB::table('product_filter_index')
            ->where('product_filter_index.category_id', $subcategory->id)
            ->whereIn('product_filter_index.product_variant_id', $poolIds) // НОВОЕ
            ->join('products', 'products.id', '=', 'product_filter_index.product_id')
            ->join('brands',   'brands.id',   '=', 'products.brand_id')
            ->select('brands.id', 'brands.title', 'brands.slug')
            ->selectRaw('COUNT(DISTINCT product_filter_index.product_variant_id) as cnt')
            ->whereNull('products.deleted_at')
            ->groupBy('brands.id', 'brands.title', 'brands.slug')
            ->orderByDesc('cnt')
            ->limit(5)
            ->get();

        foreach ($brandCounts as $brand) {
            $tags->push([
                'label'  => $brand->title,
                'url'    => route('catalog.subcategory.brand', [...$routeParams, $brand->slug]),
                'type'   => 'brand',
                'active' => $activeBrandSlug === $brand->slug,
            ]);
        }

        // ── Линейки (топ) ─────────────────────────────────────────────
        $lineupProperty = DB::table('properties')->where('slug', 'lineup')->value('id');
        if ($lineupProperty) {
            $lineups = DB::table('product_filter_index')
                ->where('product_filter_index.category_id', $subcategory->id)
                ->whereIn('product_filter_index.product_variant_id', $poolIds) // НОВОЕ
                ->where('product_filter_index.property_id', $lineupProperty)
                ->join('property_options', function ($join) use ($lineupProperty) {
                    $join->on('property_options.slug', '=', 'product_filter_index.value_slug')
                        ->where('property_options.property_id', '=', $lineupProperty);
                })
                ->select('property_options.value', 'product_filter_index.value_slug')
                ->selectRaw('COUNT(DISTINCT product_filter_index.product_variant_id) as cnt')
                ->groupBy('property_options.value', 'product_filter_index.value_slug')
                ->orderByDesc('cnt')
                ->limit(8)
                ->get();

            foreach ($lineups as $lineup) {
                $tags->push([
                    'label'  => $lineup->value,
                    'url'    => $baseUrl . '?f[lineup][]=' . urlencode($lineup->value_slug),
                    'type'   => 'lineup',
                    'active' => $activeLineupSlug === $lineup->value_slug,
                ]);
            }
        }

        // ── Популярные опции свойств (память, RAM) ────────────────────
        $featuredSlugs = ['built_in_memory', 'ram'];
        $featuredProps = DB::table('properties')
            ->whereIn('slug', $featuredSlugs)
            ->where('used_for_filters', 1)
            ->get()->keyBy('slug');

        foreach ($featuredSlugs as $propSlug) {
            $prop = $featuredProps->get($propSlug);
            if (!$prop) continue;

            $topOptions = DB::table('product_filter_index')
                ->where('product_filter_index.category_id', $subcategory->id)
                ->whereIn('product_filter_index.product_variant_id', $poolIds) // НОВОЕ
                ->where('product_filter_index.property_id', $prop->id)
                ->join('property_options', function ($join) use ($prop) {
                    $join->on('property_options.slug', '=', 'product_filter_index.value_slug')
                        ->where('property_options.property_id', '=', $prop->id);
                })
                ->select('property_options.value', 'product_filter_index.value_slug')
                ->selectRaw('COUNT(DISTINCT product_filter_index.product_variant_id) as cnt')
                ->groupBy('property_options.value', 'product_filter_index.value_slug')
                ->orderByDesc('cnt')
                ->limit(3)
                ->get();

            foreach ($topOptions as $option) {
                $tags->push([
                    'label'  => $option->value,
                    'url'    => $baseUrl . '?f[' . $propSlug . '][]=' . urlencode($option->value_slug),
                    'type'   => 'property',
                    'active' => in_array($option->value_slug, (array) ($filters['f'][$propSlug] ?? [])),
                ]);
            }
        }

        // ── Range-свойства: диагональ и ёмкость батареи ───────────────
        $rangeSlugs = ['screen_size', 'battery_capacity'];
        $rangeProps = DB::table('properties')
            ->whereIn('slug', $rangeSlugs)
            ->where('used_for_filters', 1)
            ->get()->keyBy('slug');

        foreach ($rangeSlugs as $propSlug) {
            $prop = $rangeProps->get($propSlug);
            if (!$prop) continue;

            $topValues = DB::table('product_filter_index')
                ->where('product_filter_index.category_id', $subcategory->id)
                ->whereIn('product_filter_index.product_variant_id', $poolIds) // НОВОЕ
                ->where('product_filter_index.property_id', $prop->id)
                ->whereNotNull('product_filter_index.numeric_value')
                ->join('property_options', function ($join) use ($prop) {
                    $join->on('property_options.slug', '=', 'product_filter_index.value_slug')
                        ->where('property_options.property_id', '=', $prop->id);
                })
                ->select(
                    'property_options.value',
                    'product_filter_index.value_slug',
                    'product_filter_index.numeric_value'
                )
                ->selectRaw('COUNT(DISTINCT product_filter_index.product_variant_id) as cnt')
                ->groupBy(
                    'property_options.value',
                    'product_filter_index.value_slug',
                    'product_filter_index.numeric_value'
                )
                ->orderByDesc('cnt')
                ->limit(5)
                ->get();

            $activeRangeMin = $filters['f_' . $propSlug . '_min'] ?? null;
            $activeRangeMax = $filters['f_' . $propSlug . '_max'] ?? null;

            foreach ($topValues as $val) {
                $tags->push([
                    // НОВОЕ (по желанию): для диагонали добавляем знак дюйма — «6.9"» вместо «6.9».
                    // Если не нужно, замените на просто $val->value.
                    'label'  => $val->value . ($propSlug === 'screen_size' ? '"' : ''),
                    'url'    => $baseUrl . '?' . http_build_query([
                        'f_' . $propSlug . '_min' => $val->numeric_value,
                        'f_' . $propSlug . '_max' => $val->numeric_value,
                    ]),
                    'type'   => 'range_prop',
                    'active' => $activeRangeMin == $val->numeric_value
                            && $activeRangeMax == $val->numeric_value,
                ]);
            }
        }

        // ── Toggle-свойства: NFC и беспроводная зарядка ───────────────
        $toggleSlugs = ['nfc', 'w_charg_sup'];
        $toggleProps = DB::table('properties')
            ->whereIn('slug', $toggleSlugs)
            ->where('used_for_filters', 1)
            ->get()->keyBy('slug');

        foreach ($toggleSlugs as $propSlug) {
            $prop = $toggleProps->get($propSlug);
            if (!$prop) continue;

            // НОВОЕ: проверяем наличие «yes» только среди доступных вариантов пула.
            $hasVariants = DB::table('product_filter_index')
                ->where('product_filter_index.category_id', $subcategory->id)
                ->whereIn('product_filter_index.product_variant_id', $poolIds)
                ->where('product_filter_index.property_id', $prop->id)
                ->where('product_filter_index.value_slug', 'yes')
                ->exists();

            if (!$hasVariants) continue;

            $tags->push([
                'label'  => $prop->title, // «NFC», «Поддержка беспроводной зарядки»
                'url'    => $baseUrl . '?f[' . $propSlug . '][]=yes',
                'type'   => 'toggle',
                'active' => in_array('yes', (array) ($filters['f'][$propSlug] ?? [])),
            ]);
        }

        return $tags->take($limit);
    }

    /**
    * Хелпер. Добавили в сервис приватный метод. Цена хранится в индексе, поэтому JOIN с product_variants не нужен:
    * Условие по цене варианта для запросов к product_filter_index.
    * $column указываем с префиксом таблицы/алиаса, чтобы не было неоднозначности.
    */
    private function wherePriceInIndex($query, array $filters, string $column = 'product_filter_index.price')
    {
        return $query
            ->when(!empty($filters['price_min']), fn($q) => $q->where($column, '>=', (float) $filters['price_min']))
            ->when(!empty($filters['price_max']), fn($q) => $q->where($column, '<=', (float) $filters['price_max']));
    }

}
