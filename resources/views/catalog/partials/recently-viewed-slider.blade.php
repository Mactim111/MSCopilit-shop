{{-- 
    Компонент: recently-viewed-slider
    ГЕОМЕТРИЯ: Кнопки режутся пополам (центр 16px совпадает с margin 16px слайдера).
    Слайды НЕ упираются в края 1500px, а стоят ровно по оси кнопок.
--}}

@php
    $recently_viewed_slider = $recently_viewed_slider ?? collect();
@endphp

@if($recently_viewed_slider->count() > 0)
<div class="w-full bg-white mb-[40px]">
    
    {{-- ГЛАВНЫЙ КОНТЕЙНЕР (1500px) --}}
    <div class="max-w-[1500px] mx-auto relative pt-[16px] pb-[32px]">

        {{-- 
            Кнопка НАЗАД
            left-0 — прижата к краю 1500px.
            top-[76px] — расчет центра (16pt + 12py + 48half-card).
        --}}
        <button type="button"
            class="js-swiper-prev absolute left-0 top-[76px] -translate-y-1/2
                w-[32px] h-[32px] rounded-full bg-white border border-gray-100 shadow-md 
                flex items-center justify-center cursor-pointer z-30 transition hover:shadow-lg">
            <span class="text-red-600">@include('products.icons.chevron-left-thin')</span>
        </button>

        {{-- Кнопка ВПЕРЕД --}}
        <button type="button"
            class="js-swiper-next absolute right-0 top-[76px] -translate-y-1/2
                w-[32px] h-[32px] rounded-full bg-white border border-gray-100 shadow-md 
                flex items-center justify-center cursor-pointer z-30 transition hover:shadow-lg">
            <span class="text-red-600">@include('products.icons.chevron-right-thin')</span>
        </button>

        {{-- 
            SWIPER
            mx-[16px] — ВАЖНО: это отодвигает весь блок слайдов от краев 1500px.
            Теперь край слайда проходит ровно через центр кнопки (16px).
        --}}
        <div class="js-swiper swiper overflow-hidden mx-[16px]"
             data-slides="5"
             data-space="12"
             data-loop="true"
             data-pagination="true"
             data-navigation="true">

            <div class="swiper-wrapper py-[12px]">
                @foreach($recently_viewed_slider as $variant)
                    {{-- px-[6px] для зазора между карточками --}}
                    <div class="swiper-slide flex justify-center px-[6px]">

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

        {{-- ПАГИНАЦИЯ: абсолютно в нижнем паддинге родителя --}}
        <div class="js-swiper-pagination rv-pagination !absolute !bottom-[6px] left-0 w-full flex justify-center z-10 mt-0"></div>

    </div>
</div>
@endif