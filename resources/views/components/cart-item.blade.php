@props(['item'])
@php
    $available = $item->variant->stock - $item->variant->reserved;
    $isAvailable = $available > 0;
@endphp

<div class="flex items-center w-full h-[112px] py-[16px] border-b border-dashed border-gray-200">
    <!-- Чекбокс передает ID выбранного варианта в форму массовых действий. -->
    <div class="w-[30px]">
        <input type="checkbox" name="items[]" value="{{ $item->id }}" form="cart-form"
               {{ !$isAvailable ? 'disabled' : '' }}
               class="js-item-checkbox cursor-pointer {{ !$isAvailable ? 'opacity-30' : '' }}">
    </div>

    <!-- Фото -->
    <div class="w-[80px] h-[80px] p-[4px]">
        <a href="{{ route('catalog.variant', $item->variant->slug) }}">
            <img src="{{ $item->variant->mainImage() }}" alt="{{ $item->variant->title }}"
                class="w-full h-full object-contain rounded {{ !$isAvailable ? 'grayscale' : '' }}">
        </a>
    </div>

    <!-- Название + Удалить -->
    <div class="w-[540px] pr-[40px] flex flex-col justify-center">
        <span class="font-bold text-[15px] text-[#231F20] {{ !$isAvailable ? 'text-gray-400' : '' }}">
            <a href="{{ route('catalog.variant', $item->variant->slug) }}">{{ $item->variant->title }}</a>
        </span>
        @if(!$isAvailable)
            <div class="text-[13px] text-red-600 font-semibold mt-1">Закончился</div>
        @endif
        
        {{-- Независимая форма позволяет удалить товар без установки чекбокса. --}}
        <form action="{{ route('cart.remove', $item->id) }}" method="POST">
            @csrf @method('DELETE')
            <button type="submit" class="text-[13px] text-red-600 hover:underline mt-1 w-fit">Удалить</button>
        </form>
    </div>

    <!-- Количество -->
    <div class="flex-1">
        {{-- Enter отправляет только эту форму и не запускает удаление или оформление заказа. --}}
        <form action="{{ route('cart.update', $item->id) }}" method="POST">
            @csrf @method('PUT')
            {{-- Не задаём max в HTML: превышение обрабатывается сервером и
                 возвращает сессионное сообщение с доступным остатком. --}}
            <input type="number" name="quantity" min="1"
                value="{{ $item->quantity }}"
                onchange="this.form.requestSubmit()"
                class="w-[120px] h-[40px] px-[10px] border border-gray-300 rounded-lg text-center"
                {{ !$isAvailable ? 'disabled' : '' }}>
        </form>
    </div>

    <!-- Стоимость -->
    <div class="w-[150px] flex justify-end">
        <div class="flex flex-col items-start"> 
            <div class="font-bold text-[24px]">{!! $item->formattedSubtotal(24, 24) !!}</div>
            @if($item->variant->old_price > $item->variant->price)
                <div class="flex items-center gap-2 mt-[2px]">
                    <span class="text-gray-400 text-[14px] line-through decoration-gray-400 font-semibold">
                        {!! $item->formattedOldSubtotal(14, 14) !!}
                    </span>
                    @if($item->variant->discount_percent)
                        <span class="text-red-600 text-[14px] font-bold">-{{ $item->variant->discount_percent }}%</span>
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>