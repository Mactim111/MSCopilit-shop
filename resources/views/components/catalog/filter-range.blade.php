{{-- Фильтр-диапазон для числовых свойств.
     URL: ?f_ves_min=5&f_ves_max=9
     Стиль: два инпута как у ценового фильтра, noUiSlider, красный акцент --}}

@props(['property', 'min', 'max', 'activeMin', 'activeMax'])

@if ($min <= $max)
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
        <div class="flex items-center gap-2 mb-[20px]">
            <input type="number" step="{{ $property->step }}"
                   id="range_{{ $property->slug }}_min"
                   name="f_{{ $property->slug }}_min"
                   value="{{ $activeMin ?? $min }}"
                   class="w-[154px] h-[40px] px-3 border border-gray-400 focus:border focus:border-[#231F20] focus:outline-none rounded-lg text-sm">
				   
            <input type="number" step="{{ $property->step }}"
                   id="range_{{ $property->slug }}_max"
                   name="f_{{ $property->slug }}_max"
                   value="{{ $activeMax ?? $max }}"
                   class="w-[154px] h-[40px] px-3 border border-gray-400 focus:border focus:border-[#231F20] focus:outline-none rounded-lg text-sm">
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

    // получаем Количество знаков после запятой (0, 1, 2) для ЗНАЧЕНИЙ в фильтрах и Шаг слайдера (0.01, 0.1, 1, 100, ...) из табл. 'properties'
    // (из НОВЫХ ПОЛЕЙ step и digits) отдельно! для каждого! из свойств! товара, за которое отвечает данный фильтр (типа для Диагональ экрана step = 0,01, digits = 2).
    var step   = {{ $property->step }};
    var digits = {{ $property->digits }};

    // Если min === max — показываем фиксированное значение - одинаковую сумму в обоих полях ввода - НО! БЕЗ! самого СЛАЙДЕРА! - он в таком сучае НЕ! НУЖЕН!
    if (min === max) {
        // Фиксированное значение — показываем поля, прячем слайдер.
        inputMin.value = min;
        inputMax.value = max;
        // Поля неактивныe - с серой рамкой - при фиксированном значении
        inputMin.setAttribute('readonly', 'readonly');
        inputMax.setAttribute('readonly', 'readonly');

        inputMin.classList.add('bg-gray-50', 'cursor-default', 'text-gray-400');
        inputMax.classList.add('bg-gray-50', 'cursor-default', 'text-gray-400');

        // И - Скрываем слайдер — нечего В НЕМ двигать - ЦЕНА ФИКСИРОВАННАЯ!
        slider.style.display = 'none';
    } else {
        // --- Создаём слайдер ---
        noUiSlider.create(slider, {
            start: [
                {{ (float) ($activeMin ?? $min) }}, // активное значение или минимум 
                {{ (float) ($activeMax ?? $max) }}], // активное значение или максимум
            connect: true,
            range: { min: min, max: max }, // границы слайдера = $minPrice и $maxPrice
            step: step,
            behaviour: 'tap-drag',
            // УМНОЕ! поведение фильтра - исходя из того, целое! число либо дробное! - либо сколько! имеет знаков! после запятой!
            format: {
                to: function(v) {
                    const num = parseFloat(v);

                    // Полная защита: NaN, Infinity, -Infinity
                    if (isNaN(num) || !isFinite(num)) return '';

                    // Если digits = 0 → всегда целое число
                    if (digits === 0) {
                        return Number.isInteger(num) ? num.toString() : num.toFixed(0);
                    }

                    // Если число целое → показываем без дробной части
                    if (Number.isInteger(num)) {
                        return num.toString();
                    }

                    // Число дробное → смотрим, сколько знаков после запятой у исходного значения
                    const str = v.toString();
                    const parts = str.split('.');
                    const decLen = parts[1] ? parts[1].length : 0;

                    // Если фактическая дробная часть короче или равна digits → оставляем как есть
                    // 6.1 → "6.1" (digits = 2)
                    // 6.67 → "6.67" (digits = 2)
                    if (decLen <= digits) {
                        return str;
                    }

                    // Если дробная часть длиннее digits → округляем до digits
                    // 6.678 → "6.68" (digits = 2)
                    return num.toFixed(digits);
                },
                from: function(v) {
                    return parseFloat(v);
                }
            }

        });

        // Синхронизируем поля ввода при движении ручек.
        slider.noUiSlider.on('update', function(values) {
            inputMin.value = values[0];
            inputMax.value = values[1];
        });

        // Ручной ввод в поля → обновляем позицию ручек слайдера.
        inputMin.addEventListener('change', function() { 
            slider.noUiSlider.set([parseFloat(this.value)||min, null]); 
        });
        inputMax.addEventListener('change', function() { 
            slider.noUiSlider.set([null, parseFloat(this.value)||max]); 
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

        // Enter в поле → применяем через URL (не через form.submit). Ниже - ПЕРВЫЙ - СТАРЫЙ МЕТОД - после ввода значения в каждое поле ЖМЕМ ENTER для его применения в фильтре.
        // inputMin.addEventListener('keypress', function(e) {
        //     if (e.key !== 'Enter') return;
        //     var p = {}; p['f_'+slug+'_min'] = parseFloat(this.value)||min; p['page']=null;
        //     window.location.href = buildUrl(activeBrands, p);
        // });
        // inputMax.addEventListener('keypress', function(e) {
        //     if (e.key !== 'Enter') return;
        //     var p = {}; p['f_'+slug+'_max'] = parseFloat(this.value)||max; p['page']=null;
        //     window.location.href = buildUrl(activeBrands, p);
        // });

        // --- А ниже - применили ВТОРОЙ МЕТОД - ЛОГИКА УМНОГО ПРИМЕНЕНИЯ ДИАПАЗОНА ---
            // Мы хотим:
            // 1) Ввод в первое поле — НИЧЕГО не делает.
            // 2) Ввод во второе поле + ENTER — применяет оба значения сразу.
        // --- УМНАЯ ЛОГИКА ПРИМЕНЕНИЯ ДИАПАЗОНА ---
        // Полностью аналогична ценовому фильтру.
        // Работает для любого slug: screen_size, ves, battery_capacity, etc.

        // Флаг: какое поле было изменено первым
        let firstChanged = null;

        // Когда пользователь меняет MIN — запоминаем, что MIN был изменён первым
        inputMin.addEventListener('input', function () {
            if (!firstChanged) firstChanged = 'min';
        });

        // Когда пользователь меняет MAX — запоминаем, что MAX был изменён первым
        inputMax.addEventListener('input', function () {
            if (!firstChanged) firstChanged = 'max';
        });

        // --- ENTER в MIN ---
        inputMin.addEventListener('keypress', function (e) {
            if (e.key !== 'Enter') return;

            const minVal = parseFloat(inputMin.value) || min;
            const maxVal = parseFloat(inputMax.value) || max;

            // Если MIN был изменён первым → ждём ввода MAX
            if (firstChanged === 'min') {
                // Применяем только MIN
                let p = {};
                p['f_' + slug + '_min'] = minVal;
                p['page'] = null;
                window.location.href = buildUrl(activeBrands, p);
                return;
            }

            // Если MAX был изменён первым → применяем оба значения
            let p = {};
            p['f_' + slug + '_min'] = minVal;
            p['f_' + slug + '_max'] = maxVal;
            p['page'] = null;
            window.location.href = buildUrl(activeBrands, p);
        });

        // --- ENTER в MAX ---
        inputMax.addEventListener('keypress', function (e) {
            if (e.key !== 'Enter') return;

            const minVal = parseFloat(inputMin.value) || min;
            const maxVal = parseFloat(inputMax.value) || max;

            // Если MAX был изменён первым → ждём ввода MIN
            if (firstChanged === 'max') {
                // Применяем только MAX
                let p = {};
                p['f_' + slug + '_max'] = maxVal;
                p['page'] = null;
                window.location.href = buildUrl(activeBrands, p);
                return;
            }

            // Если MIN был изменён первым → применяем оба значения
            let p = {};
            p['f_' + slug + '_min'] = minVal;
            p['f_' + slug + '_max'] = maxVal;
            p['page'] = null;
            window.location.href = buildUrl(activeBrands, p);
        });

    }

});
</script>
@endif