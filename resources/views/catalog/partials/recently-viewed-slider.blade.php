{{-- 
    Компонент: recently-viewed-slider
    ЛОГИКА: 
    - Кнопки режутся пополам: Swiper сужен на 32px (calc) и центрирован (mx-auto).
    - Центровка кнопок: top-[76px] (16px pt + 12px py слайдера + 48px половина высоты карточки).
    - Пагинация: Ровно в нижнем паддинге родителя.
--}}

@php
    $recently_viewed_slider = $recently_viewed_slider ?? collect();
@endphp

@if($recently_viewed_slider->count() > 0)
<div class="w-full bg-white">
    
    {{-- 
       ГЛАВНЫЙ КОНТЕЙНЕР (1500px)
       pt-[16px] и pb-[32px] — те самые "зеленые полосы" паддингов в консоли.
    --}}
    <div class="max-w-[1500px] mx-auto relative pt-[16px] pb-[16px]">

        {{-- 
            Кнопка НАЗАД
            left-0 — стоит на краю 1500px.
            top-[76px] — расчет: 16px (pt) + 12px (py внутри swiper) + 48px (половина высоты карточки 96).
            Теперь кнопка всегда будет "протыкать" середину карточки.
        --}}
        <button type="button"
            class="js-swiper-prev absolute left-0 top-[70px] -translate-y-1/2
                w-[32px] h-[32px] rounded-full bg-white border border-gray-100 shadow-md 
                flex items-center justify-center cursor-pointer z-30 transition hover:shadow-lg">
            <span class="text-red-600">@include('products.icons.chevron-left-thin')</span>
        </button>

        {{-- Кнопка ВПЕРЕД --}}
        <button type="button"
            class="js-swiper-next absolute right-0 top-[70px] -translate-y-1/2
                w-[32px] h-[32px] rounded-full bg-white border border-gray-100 shadow-md 
                flex items-center justify-center cursor-pointer z-30 transition hover:shadow-lg">
            <span class="text-red-600">@include('products.icons.chevron-right-thin')</span>
        </button>

        {{-- 
            SWIPER
            !w-[calc(100%-32px)] — ЖЕСТКО сужаем слайдер на ширину одной кнопки (32px).
            mx-auto — Центрируем. Это дает ровно по 16px пустоты с каждой стороны.
            Поскольку кнопка шириной 32px стоит в 0, её центр (16-й пиксель) совпадет с краем слайдера.
        --}}
        <div class="js-swiper swiper overflow-hidden !w-[calc(100%-32px)] mx-auto"
             data-slides="5"
             data-space="12"
             data-loop="true"
             data-pagination="true"
             data-navigation="true">

            <div class="swiper-wrapper py-[6px]"> {{-- py-12 защищает тени сверху и снизу --}}
                @foreach($recently_viewed_slider as $variant)
                    <div class="swiper-slide flex justify-center px-[4px]">

                        <a href="{{ route('catalog.variant', $variant->slug) }}"
                           class="rv-card w-full h-[96px] p-[8px] flex gap-[12px] 
                                border border-gray-100 rounded-lg bg-white 
                                shadow-[0_2px_8px_rgba(0,0,0,0.1)]
                                transition-all duration-300
                                hover:shadow-[0_4px_14px_rgba(0,0,0,0.22)]
                                hover:border-gray-200">

                            <div class="w-[80px] h-[80px] overflow-hidden flex-shrink-0 rounded-md bg-white">
                                <img src="{{ $variant->mainImage() }}"
                                     alt="{{ $variant->title }}"
                                     class="w-full h-full object-contain">
                            </div>

                            <div class="text-[13px] text-[#231F20] leading-tight line-clamp-3 pt-1">
                                {{ $variant->title }}
                            </div>
                        </a>

                    </div>
                @endforeach
            </div>
        </div>

        {{-- 
            ПАГИНАЦИЯ (Полоски).
            Мы вернули её как соседа .js-swiper внутри родителя max-w-[1500px].
            Теперь твой JS её увидит.
            !bottom-[6px] — кладет её точно в нижний паддинг.
        --}}
        <div class="js-swiper-pagination rv-pagination !absolute !bottom-[2px] left-0 w-full flex justify-center z-10 mt-0"></div>

    </div>
</div>
@endif