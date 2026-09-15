@extends('layouts.main')
@section('title', 'Корзина')

@section('content')
<div class="max-w-[1500px] mx-auto py-6">

    @if($items->isEmpty())
        {{-- ЗАГЛУШКА ПУСТОЙ КОРЗИНЫ --}}
        <div class="flex flex-col">
            <p class="text-[28px] text-[#231f20] mb-5 font-bold">В корзине еще нет товаров</p>
            <a href="{{ route('catalog.index') }}"
               class="inline-block text-[#007EEF] text-[15px]">
                <span class="text-[#231f20]">Выберите нужный Вам товар из </span>каталога Интернет-магазина
            </a>
        </div>
    @else
        {{-- РАБОЧАЯ КОРЗИНА --}}
        <div class="flex gap-8">
            
            <!-- ЛЕВАЯ КОЛОНКА (8/12) -->
            <div class="w-8/12">
                <div class="flex items-center mb-5 gap-4">
                    <h1 class="text-[28px] font-bold">Корзина</h1>
                    <span class="text-[20px] text-[#8c8c8c]">{{ $items->sum('quantity') }}</span>
                </div>

                <!-- ГЛОБАЛЬНАЯ ФОРМА (id="cart-form") -->
                <form id="cart-form" action="{{ route('cart.batch-actions') }}" method="POST">
                    @csrf
                    <input type="hidden" name="action" id="cart-action" value="">

                    <!-- Блок "Выбрать все" -->
                    <div class="flex items-center pb-4 justify-between">
                        <div class="flex items-center">
                            <input type="checkbox" id="select-all" class="cursor-pointer">
                            <label for="select-all" class="ml-[10px] text-[15px] font-medium text-[#231f20] cursor-pointer">Выбрать все</label>
                        </div>
                        <button type="submit" name="action" value="delete" 
                                class="text-[15px] text-[#007eff] hover:text-[#0064cc] transition-all duration-200">
                            Удалить выбранное
                        </button>
                    </div>
                    <div class="border-t border-dashed border-gray-300 w-full"></div>

                    <div>
                        @foreach($items as $item)
                            {{-- Карточка товара — все элементы внутри привязаны к cart-form --}}
                            <x-cart-item :item="$item" />
                        @endforeach
                    </div>
                </form>
            </div>

            <!-- ПРАВАЯ КОЛОНКА -->
            <aside class="w-4/12">
                <div class="sticky top-20 border border-gray-200 rounded-xl px-[24px] py-[16px] bg-white shadow-sm">
                    <div class="flex justify-between items-center mb-4 pb-4 border-b border-dashed border-gray-200">
                        <span class="text-[15px]">Товары ({{ $items->sum('quantity') }})</span>
                        <span class="font-bold text-[15px]">{!! $formattedTotal !!}</span>
                    </div>
                    <div class="flex justify-between text-[28px] font-bold mb-6">
                        <span>Итого:</span> 
                        <span class="font-bold text-3xl">{!! $formattedTotal !!}</span>
                    </div>
                    <!-- Кнопка привязана к форме через атрибут form="cart-form" -->
                    <button type="submit" form="cart-form" name="action" value="checkout"
                            class="block w-full text-center bg-red-600 text-white py-4 rounded-lg hover:bg-red-700 transition font-bold">
                        Оформить заказ
                    </button>
                </div>
            </aside>
        </div>
    @endif
</div>
@endsection

<script>
document.addEventListener('DOMContentLoaded', () => {
    const selectAll = document.getElementById('select-all');
    const checkboxes = document.querySelectorAll('.js-item-checkbox');
    
    // Логика "Выбрать все"
    if (selectAll) {
        selectAll.addEventListener('change', (e) => {
            checkboxes.forEach(cb => {
                if (!cb.disabled) cb.checked = e.target.checked;
            });
        });
    }
});
</script>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const selectAll = document.getElementById('select-all');
    const checkboxes = document.querySelectorAll('.js-item-checkbox');
    const cartForm = document.getElementById('cart-form');
    
    // --- Логика "Выбрать все" ---
    if (selectAll) {
        selectAll.addEventListener('change', (e) => {
            checkboxes.forEach(cb => {
                if (!cb.disabled) cb.checked = e.target.checked;
            });
        });
    }

    // --- Логика действий ---
    // Нам больше не нужно искать клик по кнопке, так как кнопка сама 
    // передает name="action" и value="delete/checkout" при submit формы.
    // Это стандартное поведение HTML-форм!
});
</script>