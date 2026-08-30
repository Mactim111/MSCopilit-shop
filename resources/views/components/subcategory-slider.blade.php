<div class="w-full bg-white">
    {{-- Главный контейнер-ограничитель (1500px) --}}
    <div class="max-w-[1500px] mx-auto relative py-[16px]">

        {{-- Кнопка назад --}}
        <button type="button"
            class="js-swiper-prev absolute left-[-16px] top-[45%] -translate-y-1/2
                   w-[32px] h-[32px] rounded-full bg-white border border-gray-100
                   shadow-md hover:shadow-lg transition
                   flex items-center justify-center cursor-pointer z-20">
            <span class="text-red-600">@include('products.icons.chevron-left-thin')</span>
        </button>

        {{-- Кнопка вперед --}}
        <button type="button"
            class="js-swiper-next absolute right-[-16px] top-[45%] -translate-y-1/2
                   w-[32px] h-[32px] rounded-full bg-white border border-gray-100
                   shadow-md hover:shadow-lg transition
                   flex items-center justify-center cursor-pointer z-20">
            <span class="text-red-600">@include('products.icons.chevron-right-thin')</span>
        </button>

        {{-- 
           SWIPER 
           data-space="4" + px-[6px] у слайда дадут итоговый gap в 16px между карточками.
        --}}
        <div class="js-swiper swiper w-full h-auto overflow-hidden"
            data-slides="8"
            data-space="8"
            data-loop="true"
            data-navigation="true"
            data-pagination="true"
            data-group="8">

            <div class="swiper-wrapper py-[12px] pb-[20px]"> 
                @foreach($categoriesHit as $cat)
                    @php 
                        $isReal = ($cat->products_count ?? 0) > 0;
                        // Выносим общие классы карточки, чтобы не дублировать
                        $cardClasses = "w-[174px] h-[166px] border border-gray-300 px-[16px] py-[15px] rounded-lg bg-white
                                       shadow-[0_2px_8px_rgba(0,0,0,0.1)] transition-all duration-300 block
                                       hover:shadow-[0_4px_14px_rgba(0,0,0,0.22)] hover:border-gray-200
                                       focus-visible:shadow-[0_4px_14px_rgba(0,0,0,0.22)] focus-visible:border-gray-200
                                       focus-visible:outline-none";
                    @endphp

                    {{-- Сдвигаем слайды на 6px внутрь --}}
                    <div class="swiper-slide flex justify-center px-[4px] py-[6px]">
                        
                        @if($isReal)
                            {{-- Реальная подкатегория --}}
                            <a href="{{ route('catalog.subcategory', [$cat->parent->parent->slug, $cat->parent->slug, $cat->slug]) }}"
                               class="{{ $cardClasses }}">
                        @else
                            {{-- Фейковая подкатегория (стили те же, но это span) --}}
                            <span class="{{ $cardClasses }} cursor-default">
                        @endif
                                <div class="w-full h-[90px] mb-[12px] flex items-center justify-center">
                                    <img src="{{ $cat->imageUrl() }}" class="w-[125px] h-[80px] object-contain">
                                </div>
                                <div class="w-full h-[32px] flex items-center justify-center">
                                    <span class="text-[14px] text-[#231F20] text-center leading-tight">
                                        {{ $cat->title }}
                                    </span>
                                </div>
                        @if($isReal) </a> @else </span> @endif
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Пагинация --}}
        <div class="js-swiper-pagination rv-pagination mt-[10px]"></div>

    </div>
</div>