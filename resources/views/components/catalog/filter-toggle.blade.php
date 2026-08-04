{{-- Фильтр-переключатель: «Есть NFC».
	URL: ?f[nfc][]=yes
    Стиль: красный акцент #DC092E, ширина 316px 
    Auto-submit через filterCheckboxChange() — сохраняет бренд-сегмент. 
--}}

@props(['property', 'active' => false])

<div class="w-[316px] border-b border-dashed border-gray-300 py-[14px]">
    <label for="f_toggle_{{ $property->slug }}"
        class="flex items-center justify-between cursor-pointer group">
        <span data-filter-title
              class="text-[15px] font-bold text-[#231F20] group-hover:text-[#DC092E] transition-colors">
            {{ $property->title }}
        </span>
        
		{{-- CSS-переключатель в стиле НАШЕГО проекта --}}
		<div class="relative flex-none">
            <input
                type="checkbox"
                id="f_toggle_{{ $property->slug }}"
                name="f[{{ $property->slug }}][]"
                value="yes"
                @checked($active)
                class="sr-only peer"
                onchange="filterCheckboxChange('{{ $property->slug }}')"
            >
			
			{{-- Трек переключателя --}}
            <div class="w-[40px] h-[22px] rounded-full border-2 transition-colors duration-200
                        border-gray-300 bg-gray-100
                        peer-checked:border-[#DC092E] peer-checked:bg-[#DC092E]
                        peer-focus:ring-2 peer-focus:ring-[#DC092E] peer-focus:ring-offset-1">
            </div>
			
			{{-- Ручка переключателя --}}
            <div class="absolute top-[3px] left-[3px] w-[16px] h-[16px] rounded-full bg-white shadow
                        transition-transform duration-200 peer-checked:translate-x-[18px]">
            </div>
        </div>
    </label>
</div>

