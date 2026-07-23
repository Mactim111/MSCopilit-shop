{{-- Фильтр-радио: одиночный выбор.
     URL: ?f[tip_zagruzki][]=frontalnaya
     Стиль: красный акцент #DC092E, ширина 316px --}}

@props(['property', 'active' => []])
@php $activeValue = is_array($active) ? ($active[0] ?? null) : $active; @endphp

<div
    x-data="{ open: {{ $activeValue ? 'true' : 'false' }} }"
    class="w-[316px] border-b border-dashed border-gray-300 py-[14px]"
>
    {{-- Заголовок группы data-filter-title на <span>, не на <button> — чтобы не захватить SVG --}}
    <button type="button" @click="open = !open"
        class="flex w-full items-center justify-between
               text-[15px] font-bold text-[#231F20] hover:text-[#DC092E] transition-colors">
        <span data-filter-title>{{ $property->title }}</span>
        <svg class="w-[10px] h-[6px] flex-none transition-transform duration-200"
             :class="open ? 'rotate-180' : ''"
             fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/>
        </svg>
    </button>

    {{-- Список значений --}}
    <ul x-show="open"
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 -translate-y-1"
        x-transition:enter-end="opacity-100 translate-y-0"
        class="mt-[10px] space-y-[8px]"
        x-cloak
        data-options-list>

        @foreach ($property->options as $option)
            @if (($option->products_count ?? 0) > 0)
                <li 
                    data-option-item @if($loop->index >= 6) style="display:none" @endif
                    class="flex items-center gap-[8px]"
                >
                    <input
                        type="radio"
                        id="f_{{ $property->slug }}_{{ $option->slug }}"
                        name="f[{{ $property->slug }}][]"
                        value="{{ $option->slug }}"
                        @checked($activeValue === $option->slug)
                        class="h-[16px] w-[16px] border-gray-400
                               text-[#DC092E] accent-[#DC092E] cursor-pointer
                               focus:ring-[#DC092E] focus:ring-offset-0"
                    >
                    <label for="f_{{ $property->slug }}_{{ $option->slug }}"
                        class="flex flex-1 items-center justify-between
                               text-[14px] text-[#231F20] cursor-pointer
                               hover:text-[#DC092E] transition-colors">
                        <span>{{ $option->value }}</span>
                        <span class="text-[13px] text-gray-400 ml-1">({{ $option->products_count }})</span>
                    </label>
                </li>
            @endif
        @endforeach

    </ul>

    {{-- Кнопка "Посмотреть все / Скрыть" --}}
    @if ($property->options->where('products_count', '>', 0)->count() > 6)
        <button
            type="button"
            class="flex items-center gap-[4px] text-[14px] text-[#007EFF] mt-[4px]"
            x-data="{ expanded: false }"
            @click="
                expanded = !expanded;
                const list = $el.closest('div').querySelector('[data-options-list]');
                const items = list.querySelectorAll('[data-option-item]');
                items.forEach((item, index) => {
                    item.style.display = expanded || index < 6 ? '' : 'none';
                });
            "
        >
            <span x-show="!expanded">Посмотреть все</span>
            <span x-show="expanded">Скрыть</span>

            <span class="w-[13px] h-[13px] pt-[2px]">
                <span x-show="!expanded">@include('products.icons.chevron-down')</span>
                <span x-show="expanded">@include('products.icons.chevron-up')</span>
            </span>
        </button>
    @endif

</div>
