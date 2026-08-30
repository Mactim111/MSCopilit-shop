{{-- 
    Компонент: banner-slider
    Назначение: рекламный слайдер на главной странице.
    Источник данных: таблица frontend_images (group = 'banner-slider').

    Особенности:
    - Кнопки навигации круглые 34×34, как у 5 Элемент.
    - Половина кнопки находится внутри слайдера, половина — снаружи.
    - Пагинация — полоски 32×2 px, активная красная, остальные серые.
    - Пагинация расположена на 17px от нижнего края.
    - Автопрокрутка каждые 3 секунды.
    - Инициализация происходит глобально через app.js.
--}}

<div class="max-w-[1500px] mx-auto relative margin-bottom-6">

    {{-- Кнопка назад (половина круга внутри, половина снаружи) --}}
    <button 
        class="js-swiper-prev absolute left-[1px] top-1/2 -translate-y-1/2 -translate-x-1/2
               w-[32px] h-[32px] rounded-full bg-white border border-gray-200 shadow-md
               flex items-center justify-center cursor-pointer z-20">
        <span class="text-red-600">@include('products.icons.chevron-left-thin')</span>
    </button>

    {{-- Основной контейнер SWIPER --}}
    <div class="swiper js-swiper"
         data-loop="true"
         data-pagination="true"
         data-navigation="true"
         data-slides="1"
         data-space="0"
         data-autoplay="true">

        {{-- ВАЖНО: добавили pb-[25px] --}}
        <div class="swiper-wrapper pb-[25px]">

            @foreach($one_image_banner_slider as $banner)
                <div class="swiper-slide">
                    <img src="{{ asset($banner->path) }}"
                         alt="{{ $banner->title }}"
                         class="w-full h-[300px] md:h-[450px] object-cover rounded-xl shadow-lg">
                </div>
            @endforeach

        </div>
    </div>

    {{-- ПОЛОСОЧНАЯ ПАГИНАЦИЯ --}}
    <div class="swiper-pagination rv-pagination js-swiper-pagination"></div>

    {{-- Кнопка вперед --}}
    <button 
        class="js-swiper-next absolute right-[1px] top-1/2 -translate-y-1/2 translate-x-1/2
               w-[32px] h-[32px] rounded-full bg-white border border-gray-200 shadow-md
               flex items-center justify-center cursor-pointer z-20">
        <span class="text-red-600">@include('products.icons.chevron-right-thin')</span>
    </button>

</div>
