@props(['item'])
@php

$available = $item->variant->stock - $item->variant->reserved;
$isAvailable = $available > 0;
@endphp

<div class="flex items-center w-full h-[112px] py-[16px] border-b border-dashed border-gray-200">
    <!-- Чекбокс привязан к форме cart-form по ID -->
    <div class="w-[30px]">
        <input type="checkbox" name="items[]" value="{{ $item->id }}" form="cart-form"
               {{ !$isAvailable ? 'disabled' : 'checked' }}
               class="js-item-checkbox cursor-pointer {{ !$isAvailable ? 'opacity-30' : '' }}">
    </div>

    <!-- Фото -->
    <div class="w-[80px] h-[80px] p-[4px]">
        <img src="{{ $item->variant->mainImage() }}" alt="{{ $item->variant->title }}"
            class="w-full h-full object-contain rounded {{ !$isAvailable ? 'grayscale' : '' }}">
    </div>

    <!-- Название + Удалить -->
    <div class="w-[540px] pr-[40px] flex flex-col justify-center">
        <span class="font-bold text-[15px] text-[#231F20] {{ !$isAvailable ? 'text-gray-400' : '' }}">
            {{ $item->variant->title }}
        </span>
        @if(!$isAvailable)
            <div class="text-[13px] text-red-600 font-semibold mt-1">Закончился</div>
        @endif
        
        {{-- Кнопка удаления отдельного товара из корзины --}}
        <button type="submit" 
                name="action" 
                value="remove_{{ $item->id }}"
                class="text-[13px] text-red-600 hover:underline mt-1 w-fit">
            Удалить
        </button>
    </div>

    <!-- Количество -->
    <div class="flex-1">
        <input type="number" name="quantities[{{ $item->id }}]" min="1" max="{{ $isAvailable ? $available : 1 }}"
            value="{{ $item->quantity }}"
            form="cart-form" 
            onchange="document.getElementById('cart-action').value='update'; this.form.submit()"
            class="w-[120px] h-[40px] px-[10px] border border-gray-300 rounded-lg text-center {{ !$isAvailable ? 'opacity-50' : '' }}"
            {{ !$isAvailable ? 'disabled' : '' }}>
    </div>

    <!-- Стоимость -->
    <div class="w-[150px] flex justify-end">
        <!-- 
            Обертка flex-col items-start: 
            Прижимает начало обеих строк к одной вертикальной линии (к левой стороне этой обертки).
            flex justify-end (выше): Прижимает всю эту "пачку" к правому краю ячейки.
        -->
        <div class="flex flex-col items-start">
            
            <!-- Верхняя строка (Цена) -->
            <div class="font-bold text-[24px]">
                {!! $item->formattedSubtotal(24, 24) !!}
            </div>

            <!-- Нижняя строка (Скидка) -->
            @if($item->variant->old_price > $item->variant->price)
                <div class="flex items-center gap-2 mt-[2px]">
                    <span class="text-gray-400 text-[14px] line-through decoration-gray-400 font-semibold">
                        {!! $item->formattedOldSubtotal(14, 14) !!}
                    </span>
                    @if($item->variant->discount_percent)
                        <span class="text-red-600 text-[14px] font-bold">
                            -{{ $item->variant->discount_percent }}%
                        </span>
                    @endif
                </div>
            @endif
        </div>
    </div>
    
</div>

