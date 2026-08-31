{{-- 
    Компонент: one-image-banner-slider
    Назначение: Главный рекламный баннер (1 слайд в ряд).
    
    Особенности:
    - Структура с "зелеными полосами" паддингов в консоли (как у 5 элемента).
    - Тень при фокусе/ховере (0.22 непрозрачности), без обрезания по краям.
    - Кнопки навигации точно по центру картинки, наполовину снаружи.
    - Пагинация (полоски) позиционируется абсолютно внутри нижнего паддинга.
--}}

<div class="w-full bg-white mb-[16px]"> {{-- Отступ под всем блоком слайдера --}}

    {{-- 
        ГЛАВНЫЙ КОНТЕЙНЕР (1500px)
        pt-[16px] и pb-[38px] — создают правильные отступы (зеленые зоны в консоли).
    --}}
    <div class="max-w-[1500px] mx-auto relative pt-[10px] pb-[16px]">

        {{-- 
            Кнопка назад
            top-[251px] — рассчитанный центр картинки (16pt + 10py + 225half-img).
        --}}
        <button type="button"
            class="js-swiper-prev absolute left-[-14px] top-[251px] -translate-y-1/2
                   w-[32px] h-[32px] rounded-full bg-white border border-gray-100
                   shadow-md hover:shadow-lg transition
                   flex items-center justify-center cursor-pointer z-30">
            <span class="text-red-600">@include('products.icons.chevron-left-thin')</span>
        </button>

        {{-- Кнопка вперед --}}
        <button type="button"
            class="js-swiper-next absolute right-[-14px] top-[251px] -translate-y-1/2
                   w-[32px] h-[32px] rounded-full bg-white border border-gray-100
                   shadow-md hover:shadow-lg transition
                   flex items-center justify-center cursor-pointer z-30">
            <span class="text-red-600">@include('products.icons.chevron-right-thin')</span>
        </button>

        {{-- 
            SWIPER 
            data-autoplay="true" — интервал 4с теперь берется глобально из app.js.
        --}}
        <div class="js-swiper swiper w-full overflow-hidden"
             data-loop="true"
             data-pagination="true"
             data-navigation="true"
             data-slides="1"
             data-space="0"
             data-autoplay="true">

            {{-- 
                py-[10px] — защита для верхней и нижней тени.
                px-[6px] — защита для боковых теней (чтобы не липли к краю 1500px).
            --}}
            <div class="swiper-wrapper py-[8px]">

                @foreach($one_image_banner_slider as $banner)
                    <div class="swiper-slide px-[6px]">
                        <a href="{{ $banner->link ?? '#' }}" 
                           class="block w-full h-[300px] md:h-[450px] rounded-xl overflow-hidden bg-white
                                  transition-all duration-300 shadow-none
                                  {{-- Тень при наведении/фокусе как у остальных --}}
                                  hover:shadow-[0_4px_14px_rgba(0,0,0,0.22)]
                                  focus-visible:shadow-[0_4px_14px_rgba(0,0,0,0.22)]
                                  focus-visible:outline-none">
                            
                            <img src="{{ asset($banner->path) }}"
                                 alt="{{ $banner->title }}"
                                 class="w-full h-full object-cover">
                        </a>
                    </div>
                @endforeach

            </div>
        </div>

        {{-- 
            ПОЛОСОЧНАЯ ПАГИНАЦИЯ
            Лежит абсолютно в нижнем паддинге родителя.
            bottom-[8px] — высота внутри "зеленой зоны".
        --}}
        {{-- 
            Добавляем ! перед absolute и bottom. 
            Это заставит браузер игнорировать и твой app.css, и внутренние стили Swiper,
            и слушаться только этого значения.
        --}}
        <div class="js-swiper-pagination rv-pagination !absolute !bottom-[6px] left-0 w-full flex justify-center z-20 mt-0"></div>

    </div>
</div>