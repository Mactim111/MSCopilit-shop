{{-- Фильтр-чекбокс: мультивыбор.
     Auto-submit через filterCheckboxChange() — сохраняет бренд-сегмент. --}}

@props(['property', 'active' => []])

<div
    x-data="{ open: {{ count($active) > 0 ? 'true' : 'false' }} }"
    class="w-[316px] border-b border-dashed border-gray-300 py-[14px]"
>
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
                    data-option-item
                    @if($loop->index >= 6) style="display:none" @endif
                    class="flex items-center gap-[8px]"
                >
                    <input
                        type="checkbox"
                        id="f_{{ $property->slug }}_{{ $option->slug }}"
                        name="f[{{ $property->slug }}][]"
                        value="{{ $option->slug }}"
                        @checked(in_array($option->slug, $active))
						{{--
                            onchange: при клике собираем ВСЕ отмеченные чекбоксы
                            данного свойства и обновляем URL через buildUrl().
                            buildUrl() определена в filters.blade.php глобально.
                        --}}
                        onchange="filterCheckboxChange('{{ $property->slug }}')"
                        class="cursor-pointer"
                    >
                    <label for="f_{{ $property->slug }}_{{ $option->slug }}"
                        class="flex flex-1 items-center justify-between
                               text-[15px] text-[#231F20] cursor-pointer
                               hover:text-[#DC092E] transition-colors">
                        <span>{{ $option->value }}</span>
                        <span class="text-[13px] text-gray-400 ml-1">({{ $option->products_count }})</span>
                    </label>
                </li>
            @endif
        @endforeach

    </ul>

    @if ($property->options->where('products_count', '>', 0)->count() > 6)
        <button
            type="button"
            x-show="open"
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
