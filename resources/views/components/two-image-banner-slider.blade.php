<div class="w-full bg-white mb-[70px]">
    {{-- 
       РОДИТЕЛЬ: 1500px, relative. 
       pt-[18px] и pb-[38px] создадут те самые "зеленые полосы" в консоли.
    --}}
    <div class="max-w-[1500px] mx-auto relative pt-[18px] pb-[18px]">

        {{-- 
           Кнопки: точно по центру баннера. 
           Расчет: 18px (верхний паддинг) + 10px (внутренний py слайдера) + 145px (половина высоты баннера 290) = 173px.
        --}}
        <button type="button"
            class="js-swiper-prev absolute left-[-16px] top-[173px] -translate-y-1/2
                   w-[32px] h-[32px] rounded-full bg-white border border-gray-100
                   shadow-md hover:shadow-lg transition flex items-center justify-center cursor-pointer z-20">
            <span class="text-red-600">@include('products.icons.chevron-left-thin')</span>
        </button>

        <button type="button"
            class="js-swiper-next absolute right-[-16px] top-[173px] -translate-y-1/2
                   w-[32px] h-[32px] rounded-full bg-white border border-gray-100
                   shadow-md hover:shadow-lg transition flex items-center justify-center cursor-pointer z-20">
            <span class="text-red-600">@include('products.icons.chevron-right-thin')</span>
        </button>

        {{-- SWIPER --}}
        <div class="js-swiper swiper w-full overflow-hidden"
            data-slides="2"
            data-space="12" 
            data-loop="true"
            data-navigation="true"
            data-pagination="true"
            data-autoplay="true"
            data-group="2">

            {{-- Внутренний отступ для теней оставляем --}}
            <div class="swiper-wrapper py-[6px]">
                @foreach($two_image_banner_slider as $banner)
                    <div class="swiper-slide flex justify-center px-[6px]">
                        <a href="{{ $banner->link ?? '#' }}"
                           class="w-full h-[290px] rounded-lg overflow-hidden bg-white transition-all duration-300 block 
                                  hover:shadow-[0_4px_14px_rgba(0,0,0,0.22)]">
                            <img src="{{ asset($banner->path) }}" alt="{{ $banner->title }}" class="w-full h-full object-cover">
                        </a>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- 
           ПАГИНАЦИЯ: 
           Теперь она absolute и прижата к низу родителя (bottom-[8px]). 
           Она окажется ВНУТРИ зеленого паддинга pb-[18px].
        --}}
        <div class="js-swiper-pagination rv-pagination !absolute !bottom-[6px] left-0 w-full flex justify-center z-10 mt-0"></div>

    </div>
</div>