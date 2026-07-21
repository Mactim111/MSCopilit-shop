{{-- Фильтр-диапазон для числовых свойств.
     URL: ?f_ves_min=5&f_ves_max=9
     Стиль: два инпута как у ценового фильтра, noUiSlider, красный акцент --}}

@props(['property', 'min', 'max', 'activeMin', 'activeMax'])

@if ($min < $max)
<div class="w-[316px] border-b border-dashed border-gray-300 py-[14px]"
     x-data="{ open: {{ ($activeMin != $min || $activeMax != $max) ? 'true' : 'true' }} }">

    {{-- Заголовок --}}
    <button type="button" @click="open = !open"
        class="flex w-full items-center justify-between
               text-[15px] font-bold text-[#231F20] hover:text-[#DC092E] transition-colors">
        {{ $property->title }}
        <svg class="w-[10px] h-[6px] flex-none transition-transform duration-200"
             :class="open ? 'rotate-180' : ''"
             fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/>
        </svg>
    </button>

    <div x-show="open" x-transition class="mt-[10px]" x-cloak>

        {{-- Два инпута — тот же стиль что у ценового фильтра --}}
        <div class="flex items-center gap-2 mb-[8px]">
            <input type="number"
                   id="range_{{ $property->slug }}_min"
                   name="f_{{ $property->slug }}_min"
                   value="{{ $activeMin ?? $min }}"
                   class="w-[154px] h-[40px] px-3 border border-gray-400 rounded-lg text-sm">

            <input type="number"
                   id="range_{{ $property->slug }}_max"
                   name="f_{{ $property->slug }}_max"
                   value="{{ $activeMax ?? $max }}"
                   class="w-[154px] h-[40px] px-3 border border-gray-400 rounded-lg text-sm">
        </div>

        {{-- noUiSlider (тот же что у ценового фильтра, уже подключён на странице) --}}
        <div id="range_slider_{{ $property->slug }}"
             class="w-[316px] h-[24px] pt-[8px]"></div>

    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const sliderId = 'range_slider_{{ $property->slug }}';
    const slider   = document.getElementById(sliderId);
    const inputMin = document.getElementById('range_{{ $property->slug }}_min');
    const inputMax = document.getElementById('range_{{ $property->slug }}_max');

    if (!slider || !inputMin || !inputMax) return;

    const min = {{ (float) $min }};
    const max = {{ (float) $max }};

    noUiSlider.create(slider, {
        start: [
            {{ (float) ($activeMin ?? $min) }},
            {{ (float) ($activeMax ?? $max) }}
        ],
        connect: true,
        range: { min, max },
        step: 1,
        behaviour: 'tap-drag',
        format: {
            to:   value => Math.round(value),
            from: value => Number(value),
        },
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

    // Авто-сабмит при отпускании ручки.
    slider.noUiSlider.on('change', function () {
        document.getElementById('filters-form')?.submit();
    });
});
</script>
@endif
