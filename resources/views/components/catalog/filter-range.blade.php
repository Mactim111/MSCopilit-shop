{{-- Фильтр-диапазон для числовых свойств.
     URL: ?f_ves_min=5&f_ves_max=9
     Стиль: два инпута как у ценового фильтра, noUiSlider, красный акцент --}}

@props(['property', 'min', 'max', 'activeMin', 'activeMax'])

@if ($min < $max)
<div class="w-[316px] border-b border-dashed border-gray-300 py-[14px]"
     x-data="{ open: true }">
	 
	{{-- Заголовок --}} 
    <button type="button" @click="open = !open"
        class="flex w-full items-center justify-between
               text-[15px] font-bold text-[#231F20] hover:text-[#DC092E] transition-colors">
        <span data-filter-title>{{ $property->title }}</span>
        <svg class="w-[20px] h-[12px] flex-none transition-transform duration-200"
             :class="open ? 'rotate-180' : ''"
             fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/>
        </svg>
    </button>
	
    <div x-show="open" x-transition class="mt-[10px]" x-cloak>
	
		{{-- Два инпута — тот же стиль что у ценового фильтра --}}
        <div class="flex items-center gap-2 mb-[8px]">
            <input type="number" step="0.01"
                   id="range_{{ $property->slug }}_min"
                   name="f_{{ $property->slug }}_min"
                   value="{{ $activeMin ?? $min }}"
                   class="w-[154px] h-[40px] px-3 border border-gray-400 rounded-lg text-sm">
				   
            <input type="number" step="0.01"
                   id="range_{{ $property->slug }}_max"
                   name="f_{{ $property->slug }}_max"
                   value="{{ $activeMax ?? $max }}"
                   class="w-[154px] h-[40px] px-3 border border-gray-400 rounded-lg text-sm">
        </div>
		
		{{-- noUiSlider (тот же что у ценового фильтра, уже подключён на странице) --}}
        <div id="range_slider_{{ $property->slug }}" class="w-[316px] h-[24px] pt-[8px]"></div>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var slug     = '{{ $property->slug }}';
    var slider   = document.getElementById('range_slider_' + slug);
    var inputMin = document.getElementById('range_' + slug + '_min');
    var inputMax = document.getElementById('range_' + slug + '_max');
    if (!slider || !inputMin || !inputMax) return;
    var min    = {{ (float) $min }};
    var max    = {{ (float) $max }};

    // УСЛОВИЕ если min/max не целые числа, то шаг 0.1, иначе 1 -  ЕСЛИ нужна точность до 0.1 в слайдерах фильтров типа range,
    // то можно раскомментировать эти две строки, но тогда в слайдере будет шаг 0.1
    // var step   = (min % 1 !== 0 || max % 1 !== 0) ? 0.1 : 1;
    // var digits = step < 1 ? 1 : 0;

    // ЕСЛИ нужна точность до 0.01 в слайдерах фильтров типа range
    // var step   = 0.01;
    // var digits = 2;

    // ЛИБО! Вариант Б — умный (динамическая точность) - В МЕТОДЕ ниже Определяем количество знаков после запятой у min/max и подставляем в step/digits. Е
    // сли min/max целые числа, то step=1, digits=0. Если min/max с точностью до 0.1, то step=0.1, digits=1. Если min/max с точностью до 0.01, то step=0.01, digits=2
    function countDecimals(value) {
        if (Math.floor(value) === value) return 0;
        return value.toString().split(".")[1].length;
    }

    var digits = Math.max(countDecimals(min), countDecimals(max));
    var step   = Math.pow(10, -digits);

    noUiSlider.create(slider, {
        start: [{{ (float) ($activeMin ?? $min) }}, {{ (float) ($activeMax ?? $max) }}],
        connect: true,
        range: { min: min, max: max },
        step: step,
        behaviour: 'tap-drag',
        format: {
            // toFixed() возвращает строку, поэтому parseFloat() для преобразования обратно в число
            // НИЖЕ ВАРИАНТ ЕСЛИ нужна точность до 0.1 в слайдерах фильтров типа range либо ECЛИ! применяем Динамическую Точность ЧЕРЕЗ МЕТОД countDecimals() ВЫШЕ 
            // - тогда раскомментить строку ниже и закомментить строку для РУЧНОЙ! УСТАНОВКИ точности до 0.01
            to:   function(v) { return parseFloat(v.toFixed(digits)); },
            // ЕСЛИ нужна точность до 0.01 в слайдерах фильтров типа range - если нет - закомментить строку выше и раскомментить строку ниже
            // to:   function(v) { return parseFloat(v.toFixed(2)); },
            from: function(v) { return parseFloat(v); }
        }
    });
    slider.noUiSlider.on('update', function(values) {
        inputMin.value = values[0];
        inputMax.value = values[1];
    });
    inputMin.addEventListener('change', function() { slider.noUiSlider.set([parseFloat(this.value)||min, null]); });
    inputMax.addEventListener('change', function() { slider.noUiSlider.set([null, parseFloat(this.value)||max]); });
    inputMin.addEventListener('keypress', function(e) {
        if (e.key !== 'Enter') return;
        var p = {}; p['f_'+slug+'_min'] = parseFloat(this.value)||min; p['page']=null;
        window.location.href = buildUrl(activeBrands, p);
    });
    inputMax.addEventListener('keypress', function(e) {
        if (e.key !== 'Enter') return;
        var p = {}; p['f_'+slug+'_max'] = parseFloat(this.value)||max; p['page']=null;
        window.location.href = buildUrl(activeBrands, p);
    });
	
	// Авто-сабмит при отпускании ручки.
    slider.noUiSlider.on('change', function(values) {
        var minVal = parseFloat(values[0]), maxVal = parseFloat(values[1]);
        var p = {};
        if (minVal === min && maxVal === max) {
            p['f_'+slug+'_min'] = null; p['f_'+slug+'_max'] = null;
        } else {
            p['f_'+slug+'_min'] = minVal; p['f_'+slug+'_max'] = maxVal;
        }
        p['page'] = null;
        window.location.href = buildUrl(activeBrands, p);
    });
});
</script>
@endif