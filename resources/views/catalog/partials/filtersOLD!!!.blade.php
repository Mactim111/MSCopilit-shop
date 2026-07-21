{{-- Заголовок "Фильтры" --}}
<div class="w-[316px] h-[39px] pb-[15px] flex items-center justify-between">
    <span class="text-[20px] font-bold text-[#231F20]">Фильтры</span>

    {{-- Ссылка "Очистить фильтры" (показываем только если есть фильтр) --}}
    @if(request()->has('price_min') || request()->has('price_max'))
        <button id="clear-filters-top"
                type="button"
                class="text-[14px] text-[#007EFF] pt-[2px]">
            Очистить фильтры
        </button>
    @endif
</div>

{{-- Тег применённого фильтра по цене --}}
@if(request()->has('price_min') || request()->has('price_max'))
    <div id="price-tag"
         class="w-auto max-w-[316px] bg-[#FFF4F4] border border-[#231F20] rounded-sm
                pl-[11px] pr-[6px] py-[5px] mb-[14px] flex items-center text-[15px] text-[#231F20]">

        <span id="price-tag-text" class="mr-[6px]">
            Цена: от {{ request('price_min', $minPrice) }} до {{ request('price_max', $maxPrice) }}
        </span>

        <button id="price-tag-close"
                type="button"
                class="flex items-center justify-center w-[16px] h-[16px] text-[#DC092E]">
            @include('products.icons.close-red')
        </button>
    </div>
@endif

<hr class="border-t border-dashed border-gray-300 w-[316px]">

<div class="w-[316px] py-[14px]">
    <input type="text"
           placeholder="Поиск по фильтрам"
           class="w-full h-[40px] px-3 border border-gray-400 rounded-lg focus:ring-red-500 focus:border-red-500 text-sm">
</div>

<hr class="border-t border-dashed border-gray-300 w-[316px]">

{{-- Фильтр по цене --}}
<form id="filters-form" method="GET" action="{{ route('catalog.subcategory', [$group->slug, $category->slug, $subcategory->slug]) }}">
    <div class="w-[316px] py-[14px] flex flex-col">
        <div class="text-[15px] font-bold mb-3 text-[#231F20]">Цена</div>

        <div class="flex items-center gap-2 mb-[8px]">
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
    </div>

    <div id="price-slider" class="w-[316px] h-[24px] pt-[8px]"></div>
</form>

<hr class="border-t border-dashed border-gray-300 w-[316px]">

<div class="w-[316px] h-[44px] pt-[12px] pb-[14px]">
    <a href="#" class="flex text-[15px] text-[#007EFF]">Посмотреть все
        <span class="w-[13px] h-[13px] pt-[5px]">@include('products.icons.chevron-down')</span>
    </a>
</div>

<hr class="border-t border-dashed border-gray-300 w-[316px]">

{{-- Низ: "Очистить фильтры" + "Все фильтры" --}}
<div class="w-[316px] pt-[13px] pb-[12px] flex flex-col gap-[8px]">

    @if(request()->has('price_min') || request()->has('price_max'))
        {{-- Кнопка "Очистить фильтры" --}}
        <button id="clear-filters-bottom"
                type="button"
                class="w-[316px] h-[40px] border border-[#DC092E] text-[#DC092E] font-semibold rounded-lg">
            Очистить фильтры
        </button>
    @endif

    {{-- Кнопка "Все фильтры" --}}
    <button class="w-[316px] h-[40px] bg-[#DC092E] hover:bg-red-700 text-white font-semibold rounded-lg">
        Все фильтры
    </button>

</div>


<script>
    document.addEventListener('DOMContentLoaded', function () {
        const slider   = document.getElementById('price-slider');
        const inputMin = document.getElementById('price-min');
        const inputMax = document.getElementById('price-max');

        const min = {{ $minPrice }};
        const max = {{ $maxPrice }};

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
                to: value => Math.round(value),
                from: value => Number(value)
            }
        });

        slider.noUiSlider.on('update', function (values) {
            inputMin.value = values[0];
            inputMax.value = values[1];
        });

        inputMin.addEventListener('change', function () {
            slider.noUiSlider.set([this.value || min, null]);
        });

        inputMax.addEventListener('change', function () {
            slider.noUiSlider.set([null, this.value || max]);
        });

        // --- Тег применённого фильтра по цене ---
        const priceTag      = document.getElementById('price-tag');
        const priceTagClose = document.getElementById('price-tag-close');

        if (priceTag && priceTagClose) {
            priceTagClose.addEventListener('click', () => {
                // Сбросить значения
                inputMin.value = min;
                inputMax.value = max;
                slider.noUiSlider.set([min, max]);

                // Убрать параметры из URL
                const url = new URL(window.location.href);
                url.searchParams.delete('price_min');
                url.searchParams.delete('price_max');
                window.location.href = url.toString();
            });
        }

        // --- Очистка фильтров (верх + низ) ---
        const clearTop    = document.getElementById('clear-filters-top');
        const clearBottom = document.getElementById('clear-filters-bottom');

        function clearFilters() {
            const url = new URL(window.location.href);
            url.searchParams.delete('price_min');
            url.searchParams.delete('price_max');
            window.location.href = url.toString();
        }

        if (clearTop)    clearTop.addEventListener('click', clearFilters);
        if (clearBottom) clearBottom.addEventListener('click', clearFilters);

        // --- Авто-сабмит при изменении диапазона ---
        slider.noUiSlider.on('change', function (values) {
            const minVal = Number(values[0]);
            const maxVal = Number(values[1]);

            // Если диапазон полный — считаем, что фильтр не применён
            if (minVal === min && maxVal === max) {
                const url = new URL(window.location.href);
                url.searchParams.delete('price_min');
                url.searchParams.delete('price_max');
                window.location.href = url.toString();
                return;
            }

            document.getElementById('filters-form').submit();
        });
    });
</script>
