{{--
    Сайдбар фильтров страницы подкатегории.

    Переменные из контроллера (CatalogController::subcategory()):
        $group, $category, $subcategory  — для action формы
        $minPrice, $maxPrice             — диапазон цен подкатегории (с учётом активных фильтров)
        $availableFilters                — Collection<Property> с фасетными опциями
        $filters                         — текущие активные фильтры (из validFilters() из CatalogFilterRequest)
        $brands                          — Collection<Brand>

    Принципы работы:
    1. Все фильтры — auto-submit через window.location.href: отметил чекбокс / сдвинул слайдер → страница обновляется.
    2. Все изменения идут через window.location.href (не через form.submit),
       чтобы при изменении любого фильтра остальные параметры сохранялись в URL.
    3. Кнопки «Очистить фильтры» появляются только когда есть хоть один активный фильтр
       (цена, бренд или любое свойство).
    4. Кнопка «Все фильтры» показывает скрытые группы свойств (по умолчанию видно 6).
    5. brand управляется через toggleBrand() — CSV в URL (?brand=apple,samsung).
    6. $hasActiveFilters через request()->keys() — без загрузки значений в память.
    7. Группа с активным фильтром всегда видна независимо от порога $filtersVisibleByDefault.
    8. Поиск ищет по заголовку группы И по значениям внутри группы.
       Подсвечивает жёлтым все совпадения. Группы без совпадений скрываются.

    Твой рабочий функционал сохранён полностью:
        — ценовой фильтр с noUiSlider
        — тег применённого ценового фильтра с крестиком
        — кнопки «Очистить фильтры» и «Все фильтры»
        — поиск по фильтрам

    Новый функционал добавлен под ценовым слайдером:
        — теги активных фильтров по свойствам
        — динамические блоки checkbox / radio / range / toggle
          через компоненты x-catalog.filter-*
--}}

@php
    /**
     * $hasActiveFilters — есть ли хоть один активный фильтр.
     * 
     * Расширено относительно оригинала: теперь учитываем и f[...] параметры.
     */
    // НИЖЕ ПРЕДЫДУЩАЯ ВЕРСИЯ ПОЛУЧЕНИЯ $hasActiveFilters
    // $hasActiveFilters =
    // request()->hasAny(['price_min', 'price_max']) ||
    // request()->has('brand') ||
    // collect(request()->all())->keys()->contains(
    //     fn($k) => str_starts_with($k, 'f[') || str_starts_with($k, 'f_')
    // );
    
    /**
     * Новая версия ПОЛУЧЕНИЯ $hasActiveFilters
     * Используем request()->keys() вместо request()->all(),
     * чтобы не загружать в память значения всех параметров —
     * нам нужны только ключи для проверки.
     *
     * Покрывает три источника активных фильтров:
     *   1. Ценовой фильтр        — price_min / price_max
     *   2. Фильтр бренда         — brand
     *   3. Фильтры по свойствам  — f[slug][] и f_slug_min / f_slug_max
     */
    $hasActiveFilters = collect(request()->keys())->contains(
        fn($k) =>
            $k === 'price_min'       ||
            $k === 'price_max'       ||
            $k === 'brand'           ||
            str_starts_with($k, 'f[') ||
            str_starts_with($k, 'f_')
    );

    /**
     * Порог количества видимых групп фильтров по умолчанию.
     * Остальные скрыты и раскрываются кнопкой «Все фильтры».
     * Чтобы изменить порог — поменяйте только эту константу.
     */
    $filtersVisibleByDefault = 6;

@endphp

{{-- ── Заголовок "Фильтры" + кнопка очистки вверху ──────────────── --}}
<div class="w-[316px] h-[39px] pb-[15px] flex items-center justify-between">
    <span class="text-[20px] font-bold text-[#231F20]">Фильтры</span>

    @if($hasActiveFilters)
        <button id="clear-filters-top"
                type="button"
                class="text-[14px] text-[#007EFF] pt-[2px]">
            Очистить фильтры
        </button>
    @endif
</div>

<hr class="border-t border-dashed border-gray-300 w-[316px]">

{{-- ── Теги применённых фильтров ─────────────────────────────────── --}}

{{-- Тег ценового фильтра (твой оригинальный) --}}
@if(request()->hasAny(['price_min', 'price_max']))
    <div class="filter-prop-tag bg-gray-100 border border-[#231F20] rounded-sm
                pl-[11px] pr-[6px] py-[5px] mb-[8px] flex items-center text-[15px] text-[#231F20]"
         data-param="price">
        <span class="mr-[6px]">
            Цена: от {{ request('price_min', $minPrice) }} до {{ request('price_max', $maxPrice) }}
        </span>
        <button type="button"
                class="filter-tag-close flex items-center justify-center w-[16px] h-[16px] text-[#DC092E]">
            @include('products.icons.close-red')
        </button>
    </div>
@endif

{{-- Теги брендов --}}
@if(request()->has('brand'))
    @php $brandSlugs = array_filter(explode(',', request('brand'))); @endphp
    @foreach($brandSlugs as $slug)
        @php $brandTitle = $brands->firstWhere('slug', $slug)?->title; @endphp
        @if($brandTitle)
            <div class="filter-prop-tag border border-[#231F20] rounded-sm
                        pl-[11px] pr-[6px] py-[5px] mb-[8px] flex items-center
                        text-[15px] text-[#231F20]"
                 data-param="brand-csv"
                 data-value="{{ $slug }}">
                <span class="mr-[6px]">Бренд: {{ $brandTitle }}</span>
                <button type="button"
                        class="filter-tag-close flex items-center justify-center w-[16px] h-[16px] text-[#DC092E]">
                    @include('products.icons.close-red')
                </button>
            </div>
        @endif
    @endforeach
@endif

{{-- Теги активных фильтров по свойствам --}}
@if(isset($availableFilters))
    @foreach($availableFilters as $property)
        @php
            $activeValues = $filters['f'][$property->slug] ?? [];
            if (!is_array($activeValues)) $activeValues = [$activeValues];
        @endphp
        @foreach($activeValues as $activeSlug)
            @php $optionLabel = $property->options->firstWhere('slug', $activeSlug)?->value; @endphp
            @if($optionLabel)
                <div class="filter-prop-tag border border-[#231F20] rounded-sm
                            pl-[11px] pr-[6px] py-[5px] mb-[8px] flex items-center
                            text-[15px] text-[#231F20]"
                     data-param="f-array"
                     data-key="f[{{ $property->slug }}][]"
                     data-value="{{ $activeSlug }}">
                    <span class="mr-[6px]">{{ $property->title }}: {{ $optionLabel }}</span>
                    <button type="button"
                            class="filter-tag-close flex items-center justify-center w-[16px] h-[16px] text-[#DC092E]">
                        @include('products.icons.close-red')
                    </button>
                </div>
            @endif
        @endforeach
    @endforeach
@endif

{{-- ── Поиск по фильтрам ──────────────────────────────────────────── --}}
<div class="w-[316px] py-[14px] relative">
    <input type="text"
           id="filter-search"
           autocomplete="off"
           placeholder="Поиск по фильтрам"
           class="w-full h-[40px] pl-3 pr-9 border border-gray-400 rounded-lg
                  focus:outline-none focus:border-[#DC092E] text-[14px]
                  placeholder:text-gray-400">
    {{-- Крестик очистки — скрыт по умолчанию, появляется при вводе --}}
    <button type="button" id="filter-search-clear"
            class="absolute right-3 top-1/2 -translate-y-1/2
                   hidden text-gray-400 hover:text-[#DC092E] transition-colors"
                   aria-label="Очистить поиск">
        <svg class="w-[14px] h-[14px]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                  d="M6 18L18 6M6 6l12 12"/>
        </svg>
    </button>
</div>

<hr class="border-t border-dashed border-gray-300 w-[316px]">

{{-- ── ФОРМА: цена + бренды + свойства ───────────────────────────── --}}
<form id="filters-form"
      method="GET"
      action="{{ route('catalog.subcategory', [$group->slug, $category->slug, $subcategory->slug]) }}">

    @if(request('sort'))
        <input type="hidden" name="sort" value="{{ request('sort') }}">
    @endif

    {{-- ── Цена ──────────────────────────────────────────────────── --}}
    <div class="w-[316px] py-[14px] flex flex-col">
        <div class="text-[15px] font-bold mb-3 text-[#231F20]">Цена</div>

        <div class="flex items-center gap-2 mb-[16px]">
            <input type="number"
                   id="price-min"
                   name="price_min"
                   value="{{ request('price_min', $minPrice) }}"
                   class="w-[154px] h-[40px] px-3 border border-gray-400 rounded-lg text-sm">
            <input type="number"
                   id="price-max"
                   name="price_max"
                   value="{{ request('price_max', $maxPrice) }}"
                   class="w-[154px] h-[40px] px-3 border border-gray-400 rounded-lg text-sm">
        </div>

        <div id="price-slider" class="w-[316px] h-[24px] pt-[8px]"></div>
    </div>

    <hr class="border-t border-dashed border-gray-300 w-[316px]">

    {{-- ── Бренды ─────────────────────────────────────────────────── --}}

    @if(isset($brands) && $brands->isNotEmpty())
        {{--
            data-title="бренд" — для поиска по заголовку группы.
            Значения брендов доступны через [data-brand-item] — для поиска по значениям.
        --}}
        <div class="filter-section w-[316px]" data-title="бренд">
            <x-catalog.filter-brand
                :brands="$brands"
                :activeBrands="array_filter(explode(',', request('brand', '')))"
            />
        </div>
    @endif

    {{-- ── Свойства ───────────────────────────────────────────────── --}}
    @if(isset($availableFilters) && $availableFilters->isNotEmpty())
        @foreach($availableFilters as $index => $property)
            {{--
                Первые $filtersVisibleByDefault групп видны сразу.
                Остальные скрыты классом filter-extra — раскрываются кнопкой «Все фильтры».
                Чтобы изменить порог — поменяйте $filtersVisibleByDefault в ДИРЕКТИВЕ php вверху файла.
            --}}

            @php
                $activeValues = $filters['f'][$property->slug] ?? [];
                $activeMin    = $filters['f_' . $property->slug . '_min'] ?? null;
                $activeMax    = $filters['f_' . $property->slug . '_max'] ?? null;
 
                // Группа считается активной если есть выбранные значения или range.
                $hasActive = !empty($activeValues) || $activeMin !== null || $activeMax !== null;
 
                // Скрываем группу если: индекс за порогом И нет активных значений.
                // Активные группы ВСЕГДА видны — пользователь должен видеть свои фильтры.
                $isHidden  = $index >= $filtersVisibleByDefault && !$hasActive;
            @endphp

            <div class="filter-section w-[316px] {{ $isHidden ? 'filter-extra' : '' }}"
                 data-title="{{ mb_strtolower($property->title) }}"
                 @if($isHidden) style="display:none" @endif>
                <x-catalog.filter-group
                    :property="$property"
                    :active="$activeValues"
                    :active-min="$activeMin"
                    :active-max="$activeMax"
                />
            </div>
        @endforeach
    @endif

    {{-- ── Кнопки в подвале сайдбара ─────────────────────────────── --}}
    <hr class="border-t border-dashed border-gray-300 w-[316px] mt-[4px]">

    <div class="w-[316px] pt-[13px] pb-[12px] flex flex-col gap-[8px]">

        {{-- «Очистить фильтры» — только когда есть активный фильтр --}}
        @if($hasActiveFilters)
            <button id="clear-filters-bottom" type="button"
                    class="w-[316px] h-[40px] border border-[#DC092E]
                           text-[#DC092E] font-semibold rounded-lg">
                Очистить фильтры
            </button>
        @endif

        {{--
            «Все фильтры» — раскрывает скрытые группы свойств.
            Скрывается сама, если скрытых групп нет (JS-логика ниже).
        --}}
        <button id="btn-all-filters" type="button"
                class="w-[316px] h-[40px] bg-[#DC092E] hover:bg-red-700
                       text-white font-semibold rounded-lg">
            Все фильтры
        </button>

    </div>

</form>

<script>
document.addEventListener('DOMContentLoaded', function () {

    // ================================================================
    // ВСПОМОГАТЕЛЬНЫЕ ФУНКЦИИ — единый способ обновления URL
    // ================================================================
    //    ВНИМАНИЕ! ДАННЫЙ МЕТОД ОТСУТСТВУЕТ В ТЕКУЩЕЙ ВЕРСИИ КОМПОНЕНТА, которой в поиске добавлен поиск по ЗНАЧЕНИЮ ФИЛЬТРА (ТИПА красный), 
    // а также подсвечивание совпадения внутри значений фильтров и показ секции фильтров если совпадение найдено хотя бы в одном значении, также
    // теперь группа с активным фильтром всегда видна — если у свойства есть активные значения, оно всегда видно независимо от порога, устанавливающего 
    // число секций (групп) фильтров, показываемых в сайдбаре фильтров по умолч. (6 шт. - остальные открываются кликом по "Все фильтры") !
    /**
     * Обновляет один параметр в текущем URL и переходит по новому адресу.
     * Все остальные параметры (brand, f[], sort и т.д.) сохраняются.
     * value === null → параметр удаляется из URL.
     
    function setUrlParam(key, value) {
        const url = new URL(window.location.href);
        url.searchParams.delete('page'); // при изменении фильтра возвращаемся на стр. 1
        value === null
            ? url.searchParams.delete(key)
            : url.searchParams.set(key, value);
        window.location.href = url.toString();
    }
    */

    // ================================================================
    // ЦЕНОВОЙ СЛАЙДЕР
    // ================================================================
    const slider   = document.getElementById('price-slider');
    const inputMin = document.getElementById('price-min');
    const inputMax = document.getElementById('price-max');
    const min      = {{ $minPrice }};
    const max      = {{ $maxPrice }};

    if (slider && min < max) {
        noUiSlider.create(slider, {
            start: [
                {{ request('price_min', $minPrice) }},
                {{ request('price_max', $maxPrice) }}
            ],
            connect: true,
            range: { min, max },
            step: 1,
            behaviour: 'tap-drag',
            format: {
                to:   v => Math.round(v),
                from: v => Number(v),
            },
        });

        // Синхронизируем поля ввода при движении ручек.
        slider.noUiSlider.on('update', (values) => {
            inputMin.value = values[0];
            inputMax.value = values[1];
        });

        // Ручной ввод в поля → обновляем позицию ручек слайдера.
        inputMin.addEventListener('change', function () {
            slider.noUiSlider.set([this.value || min, null]);
        });
        inputMax.addEventListener('change', function () {
            slider.noUiSlider.set([null, this.value || max]);
        });

        // Enter в поле → применяем через URL (не через form.submit).
        inputMin.addEventListener('keypress', function (e) {
            if (e.key !== 'Enter') return;
            const url = new URL(window.location.href);
            url.searchParams.delete('page');
            url.searchParams.set('price_min', this.value || min);
            window.location.href = url.toString();
        });
        inputMax.addEventListener('keypress', function (e) {
            if (e.key !== 'Enter') return;
            const url = new URL(window.location.href);
            url.searchParams.delete('page');
            url.searchParams.set('price_max', this.value || max);
            window.location.href = url.toString();
        });

        // Отпустили ручку слайдера → применяем через URL.
        // НЕ через form.submit() — иначе brand и f[] потеряются.
        slider.noUiSlider.on('change', (values) => {
            const minVal = Number(values[0]);
            const maxVal = Number(values[1]);
            const url    = new URL(window.location.href);

            url.searchParams.delete('page');

            if (minVal === min && maxVal === max) {
                url.searchParams.delete('price_min');
                url.searchParams.delete('price_max');
            } else {
                url.searchParams.set('price_min', minVal);
                url.searchParams.set('price_max', maxVal);
            }

            window.location.href = url.toString();
        });
    }

    // ================================================================
    // УДАЛЕНИЕ ТЕГОВ (крестики)
    // ================================================================
    document.querySelectorAll('.filter-tag-close').forEach(btn => {
        btn.addEventListener('click', () => {
            const tag   = btn.closest('.filter-prop-tag');
            const param = tag.dataset.param;
            const url   = new URL(window.location.href);
            url.searchParams.delete('page');

            if (param === 'price') {
                url.searchParams.delete('price_min');
                url.searchParams.delete('price_max');

            } else if (param === 'brand-csv') {
                const current = (url.searchParams.get('brand') || '').split(',').filter(Boolean);
                const updated = current.filter(b => b !== tag.dataset.value);
                updated.length
                    ? url.searchParams.set('brand', updated.join(','))
                    : url.searchParams.delete('brand');

            } else if (param === 'f-array') {
                const key      = tag.dataset.key;
                const existing = url.searchParams.getAll(key);
                url.searchParams.delete(key);
                existing
                    .filter(v => v !== tag.dataset.value)
                    .forEach(v => url.searchParams.append(key, v));
            }

            window.location.href = url.toString();
        });
    });

    // ================================================================
    // ОЧИСТКА ВСЕХ ФИЛЬТРОВ
    // ================================================================
    function clearAllFilters() {
        const url = new URL(window.location.href);
        
        // Удаляем все параметры фильтров одним проходом.
        [...url.searchParams.keys()]
            .filter(k =>
                k === 'price_min' || k === 'price_max' || k === 'brand' ||
                k === 'page'      || k.startsWith('f[') || k.startsWith('f_')
            )
            .forEach(k => url.searchParams.delete(k));
        window.location.href = url.toString();
    }

    const clearTop    = document.getElementById('clear-filters-top');
    const clearBottom = document.getElementById('clear-filters-bottom');
    if (clearTop)    clearTop.addEventListener('click', clearAllFilters);
    if (clearBottom) clearBottom.addEventListener('click', clearAllFilters);

    // ================================================================
    // КНОПКА «ВСЕ ФИЛЬТРЫ»
    //
    // По умолчанию первые {{ $filtersVisibleByDefault }} групп видны,
    // остальные скрыты (class="filter-extra", style="display:none").
    //
    // При клике:
    //   — показываем все скрытые группы
    //   — кнопку скрываем (сворачивания нет, как решили)
    //
    // Если скрытых групп нет изначально — кнопку тоже скрываем,
    // чтобы не занимала место зря.
    // ================================================================
    const btnAllFilters = document.getElementById('btn-all-filters');
    const extraSections = document.querySelectorAll('.filter-extra');

    if (btnAllFilters) {
        if (extraSections.length === 0) {
            // Скрытых групп нет — кнопка не нужна.
            btnAllFilters.style.display = 'none';
        } else {
            btnAllFilters.addEventListener('click', () => {
                extraSections.forEach(section => {
                    section.style.display = '';
                    section.style.opacity = '1';
                    section.style.maxHeight = '';
                    section.style.overflow  = '';
                    section.style.pointerEvents = '';
                    section.classList.remove('filter-extra');
                });
                // Скрываем кнопку после раскрытия — сворачивания нет.
                btnAllFilters.style.display = 'none';
            });
        }
    }

    // ================================================================
    // САБМИТ ФОРМЫ — переводим в URL чтобы не потерять brand
    // ================================================================
    const form = document.getElementById('filters-form');
    if (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();

            const url      = new URL(window.location.href);
            const formData = new FormData(this);

            url.searchParams.delete('page');

            // Удаляем старые параметры формы (цена, свойства, sort).
            // brand в URL не трогаем — он управляется через toggleBrand().
            ['price_min', 'price_max', 'sort'].forEach(k => url.searchParams.delete(k));
            [...url.searchParams.keys()]
                .filter(k => k.startsWith('f[') || k.startsWith('f_'))
                .forEach(k => url.searchParams.delete(k));

            for (const [key, value] of formData.entries()) {
                key === 'sort'
                    ? url.searchParams.set(key, value)
                    : url.searchParams.append(key, value);
            }

            window.location.href = url.toString();
        });
    }

    // ================================================================
    // ПОИСК ПО ФИЛЬТРАМ
    // ================================================================
    //
    // Логика (как на 5 ЭЛЕМЕНТ):
    //   — Фильтрует .filter-section по data-title без перезагрузки страницы
    // Ищет по:
    //   1. data-title секции (заголовок группы) — «Цвет корпуса», «Бренд»
    //   2. [data-option-item] внутри секции — значения свойств («Красный», «Samsung»)
    //   3. [data-brand-item] внутри секции бренда
    // Если совпадение найдено хотя бы в одном месте — секция видна.
    // Жёлтая подсветка ставится на все совпадающие элементы.
    //   — Совпадающая часть заголовка подсвечивается жёлтым фоном
    //   — При пустом поле — все секции видны, подсветка снята
    //   — Крестик справа в поле: появляется при вводе, сбрасывает поиск
    //
    const filterSearch      = document.getElementById('filter-search');
    const filterSearchClear = document.getElementById('filter-search-clear');
    const filterSections    = document.querySelectorAll('.filter-section');

    if (!filterSearch) return; // поле не найдено — выходим

    // Функция подсветки: оборачивает найденный текст (в заголовках фильтров и их значениях) в <mark>.
    // Работает с оригинальным textContent заголовка, хранящимся в data-original.
    // Сохраняет оригинальный innerHTML в data-original при первом вызове.
    // При пустом query восстанавливает оригинал.
    function highlightEl(el, query) {
        if (!el.dataset.original) {
            el.dataset.original = el.innerHTML;
        }
        if (!query) {
            el.innerHTML = el.dataset.original;
            return false; // совпадений нет (пустой запрос)
        }
        const original = el.dataset.original;
        // Экранируем спецсимволы regex.
        const escaped = query.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
        const regex   = new RegExp(`(${escaped})`, 'gi');
        const hasMatch = regex.test(original);

        el.innerHTML = original.replace(
            new RegExp(`(${escaped})`, 'gi'),
            '<mark style="background:#FFE600;color:#231F20;border-radius:2px;padding:0 1px;">$1</mark>'
        );
        return hasMatch;
    }


    // Показывает/скрывает секцию.
    // Используем opacity+maxHeight вместо display:none, чтобы не конфликтовать с Alpine.js x-show внутри компонентов.

    function setVisible(section, visible) {
        section.style.opacity       = visible ? '1' : '0';
        section.style.overflow      = visible ? '' : 'hidden';
        section.style.maxHeight     = visible ? '' : '0';
        section.style.pointerEvents = visible ? '' : 'none';
    }

    // Функция поиска — вызывается на каждый input.
    function runSearch(query) {
        const q = query.toLowerCase().trim();

        filterSections.forEach(section => {
            // --- 1. Проверяем заголовок группы ---
            const titleEl    = section.querySelector('[data-filter-title]');
            const titleMatch = titleEl ? highlightEl(titleEl, q) : false;
            // Для пустого запроса восстанавливаем заголовок
            if (!q && titleEl) titleEl.innerHTML = titleEl.dataset.original || titleEl.innerHTML;

            // --- 2. Проверяем значения (опции свойств и бренды) ---
            // [data-option-item] — значения checkbox/radio (filter-checkbox, filter-radio)
            // [data-brand-item]  — элементы бренда (filter-brand)
            const valueItems = section.querySelectorAll('[data-option-item], [data-brand-item]');
            let valueMatch   = false;

            valueItems.forEach(item => {
                // Подсвечиваем только текстовый span внутри label,
                // чтобы не задеть чекбокс и счётчик.
                const textEl = item.querySelector('label span:first-child') || item.querySelector('label');
                if (!textEl) return;

                const matched = q ? highlightEl(textEl, q) : false;
                if (!q && textEl.dataset.original) {
                    textEl.innerHTML = textEl.dataset.original;
                }

                // Показываем/скрываем отдельный элемент списка.
                // При пустом запросе возвращаем к исходной видимости
                // (учитываем data-option-item с изначальным display:none).
                if (q) {
                    item.style.display = matched ? '' : 'none';
                    if (matched) valueMatch = true;
                } else {
                    // Восстанавливаем исходный display из data-original-display
                    item.style.display = item.dataset.originalDisplay ?? '';
                }
            });

            // Запоминаем исходный display при первом проходе.
            if (!q) {
                // Ничего не делаем — уже восстановлено выше.
            }

            // --- 3. Показываем/скрываем всю секцию ---
            if (!q) {
                // Пустой запрос — восстанавливаем исходную видимость секции.
                // filter-extra секции остаются скрытыми (их восстанавливает кнопка «Все фильтры»).
                if (!section.classList.contains('filter-extra')) {
                    setVisible(section, true);
                }
            } else {
                setVisible(section, titleMatch || valueMatch);
            }
        });

        if (filterSearchClear) {
            filterSearchClear.classList.toggle('hidden', !q);
        }
    }

    // Запоминаем исходный display каждого [data-option-item] и [data-brand-item]
    // до первого поиска, чтобы корректно восстанавливать после сброса.
    filterSections.forEach(section => {
        section.querySelectorAll('[data-option-item], [data-brand-item]').forEach(item => {
            item.dataset.originalDisplay = item.style.display;
        });
    });

    filterSearch.addEventListener('input', function () {
        runSearch(this.value);
    });

    // Крестик — сбрасывает поиск и возвращает все секции.
    if (filterSearchClear) {
        filterSearchClear.addEventListener('click', () => {
            filterSearch.value = '';
            filterSearch.focus();
            runSearch('');
        });
    }

});

// ================================================================
// toggleBrand() — глобальная, вызывается из filter-brand.blade.php
// ================================================================
function toggleBrand(slug) {
    const url      = new URL(window.location.href);
    const brandStr = url.searchParams.get('brand') || '';
    let   brands   = brandStr.split(',').filter(Boolean);

    brands.includes(slug)
        ? brands = brands.filter(b => b !== slug)
        : brands.push(slug);

    brands.length
        ? url.searchParams.set('brand', brands.join(','))
        : url.searchParams.delete('brand');

    url.searchParams.delete('page');
    window.location.href = url.toString();
}
</script>