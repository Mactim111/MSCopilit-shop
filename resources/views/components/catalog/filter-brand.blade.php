{{-- Фильтр брендов — с ограничением в 2 значения и кнопкой "Посмотреть все / Скрыть" --}}
@props([
    'brands' => [],
    'activeBrands' => [], // массив SLUG-ов активных брендов
])

<div
    x-data="{ open: true }"
    class="w-[316px] border-b border-dashed border-gray-300 py-[14px]"
>
    {{-- Заголовок группы --}}
    <button type="button" @click="open = !open"
        class="flex w-full items-center justify-between
               text-[15px] font-bold text-[#231F20] hover:text-[#DC092E] transition-colors"
    data-filter-title>
        Бренд
        <svg class="w-[10px] h-[6px] flex-none transition-transform duration-200"
             :class="open ? 'rotate-180' : ''"
             fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/>
        </svg>
    </button>

    {{-- Список брендов --}}
    <ul x-show="open"
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 -translate-y-1"
        x-transition:enter-end="opacity-100 translate-y-0"
        class="mt-[10px] space-y-[8px]"
        x-cloak
        data-brand-list
    >
        @foreach ($brands as $brand)
            <li 
                data-brand-item
                @if($loop->index >= 2) style="display:none" @endif
                class="flex items-center gap-[8px] cursor-pointer"
                onclick="toggleBrand('{{ $brand->slug }}')"
            >
                <input
                    type="checkbox"
                    name="brand[]"
                    value="{{ $brand->slug }}"
                    @checked(in_array($brand->slug, $activeBrands))
                    class="h-[16px] w-[16px] border-gray-400
                           text-[#DC092E] accent-[#DC092E] cursor-pointer
                           focus:ring-[#DC092E] focus:ring-offset-0"
                >

                <label class="flex flex-1 items-center justify-between
                           text-[14px] text-[#231F20] cursor-pointer
                           hover:text-[#DC092E] transition-colors">
                    <span>{{ $brand->title }}</span>
                </label>
            </li>
        @endforeach
    </ul>

    {{-- Кнопка "Посмотреть все / Скрыть" --}}
    @if (count($brands) > 2)
        <button
            type="button"
            class="flex items-center gap-[4px] text-[14px] text-[#007EFF] mt-[4px]"
            x-data="{ expanded: false }"
            @click="
                expanded = !expanded;
                const list = $el.closest('div').querySelector('[data-brand-list]');
                const items = list.querySelectorAll('[data-brand-item]');
                items.forEach((item, index) => {
                    item.style.display = expanded || index < 2 ? '' : 'none';
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
