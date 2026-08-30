{{-- Слайдер "Хиты продаж" (Двойные баннеры) --}}
@if(isset($banner_bestsellers) && $banner_bestsellers->count() > 4)
<div class="w-full bg-white mb-[40px]"> {{-- Отступ 40px ПОД всем блоком --}}
    
    {{-- Главный контейнер 1500px. py-[18px] задает отступы сверху и снизу --}}
    <div class="max-w-[1500px] mx-auto relative pt-[18px] pb-[18px]">

        {{-- Кнопка назад (Наполовину снаружи контейнера) --}}
        <button type="button"
            class="js-swiper-prev absolute left-[-16px] top-1/2 -translate-y-1/2
                   w-[32px] h-[32px] rounded-full bg-white border border-gray-100
                   shadow-md hover:shadow-lg transition
                   flex items-center justify-center cursor-pointer z-20">
            <span class="text-red-600">@include('products.icons.chevron-left-thin')</span>
        </button>

        {{-- Кнопка вперед (Наполовину снаружи контейнера) --}}
        <button type="button"
            class="js-swiper-next absolute right-[-16px] top-1/2 -translate-y-1/2
                   w-[32px] h-[32px] rounded-full bg-white border border-gray-100
                   shadow-md hover:shadow-lg transition
                   flex items-center justify-center cursor-pointer z-20">
            <span class="text-red-600">@include('products.icons.chevron-right-thin')</span>
        </button>

        {{-- 
           SWIPER 
           - data-slides="2": Показываем 2 слайда
           - data-group="2": Листаем секциями по 2
           - data-space="12": В сумме с паддингами слайдов даст зазор 24px между баннерами
           - data-autoplay="true": Включает автопрокрутку (задержка меняется в app.js)
        --}}
        <div class="js-swiper swiper w-full overflow-hidden"
            data-slides="2"
            data-space="12" 
            data-loop="true"
            data-navigation="true"
            data-pagination="true"
            data-autoplay="true"
            data-group="2">

            <div class="swiper-wrapper py-[10px]"> {{-- py-[10px] дает место для тени, чтобы она не обрезалась сверху/снизу --}}
                
                @foreach($two_image_banner_slider as $banner)
                    {{-- 
                       px-[6px]: "Утапливает" слайд внутрь на 6px. 
                       Это нужно, чтобы тень крайнего слайда не съедалась границей контейнера 1500px.
                    --}}
                    <div class="swiper-slide flex justify-center px-[6px]">

                        <a href="{{ $banner->link ?? '#' }}"
                           class="w-full h-[290px] rounded-lg overflow-hidden bg-white
                                  transition-all duration-300 block
                                  {{-- Изначально тени и рамки нет --}}
                                  shadow-none 
                                  {{-- При фокусе/ховере появляется тень как у предыдущего слайдера, без рамки --}}
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
           Пагинация (Полоски). 
           Их количество (3 при 6 баннерах) Swiper рассчитает сам из-за data-group="2".
           mt-[12px] регулирует отступ полосок от нижнего края картинок.
        --}}
        <div class="js-swiper-pagination rv-pagination mt-[12px]"></div>

    </div>
</div>
@endif