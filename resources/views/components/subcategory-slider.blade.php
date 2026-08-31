<div class="w-full bg-white mb-13">
    {{-- 
       ГЛАВНЫЙ КОНТЕЙНЕР:
       pt-[16px] и pb-[16px] создают те самые "зеленые полосы" (паддинги) в консоли.
    --}}
    <div class="max-w-[1500px] mx-auto relative pt-[16px] pb-[16px]">

        {{-- 
           Кнопки: точно по центру карточки. 
           Расчет: 16px (верхний паддинг) + 12px (внутренний py wrapper-а) + 83px (половина высоты карточки 166) = 111px.
        --}}
        <button type="button"
            class="js-swiper-prev absolute left-[-16px] top-[111px] -translate-y-1/2
                   w-[32px] h-[32px] rounded-full bg-white border border-gray-100 shadow-md 
                   flex items-center justify-center cursor-pointer z-20 transition hover:shadow-lg">
            <span class="text-red-600">@include('products.icons.chevron-left-thin')</span>
        </button>

        <button type="button"
            class="js-swiper-next absolute right-[-16px] top-[111px] -translate-y-1/2
                   w-[32px] h-[32px] rounded-full bg-white border border-gray-100 shadow-md 
                   flex items-center justify-center cursor-pointer z-20 transition hover:shadow-lg">
            <span class="text-red-600">@include('products.icons.chevron-right-thin')</span>
        </button>

        {{-- SWIPER --}}
        <div class="js-swiper swiper w-full h-auto overflow-hidden"
            data-slides="8" 
            data-space="8" 
            data-loop="true" 
            data-navigation="true" 
            data-pagination="true" 
            data-group="8">

            {{-- Внутренний паддинг wrapper-а для сохранения теней сверху и снизу --}}
            <div class="swiper-wrapper py-[8px]"> 
                @foreach($categoriesHit as $cat)
                    @php 
                        $isReal = ($cat->products_count ?? 0) > 0;
                        // Классы карточки: тень при наведении, закругление, плавность
                        $cardClasses = "w-[174px] h-[166px] border border-gray-300 px-[16px] py-[15px] rounded-lg bg-white
                                       shadow-[0_2px_8px_rgba(0,0,0,0.1)] transition-all duration-300 block
                                       hover:shadow-[0_4px_14px_rgba(0,0,0,0.22)] hover:border-gray-200
                                       focus-visible:shadow-[0_4px_14px_rgba(0,0,0,0.22)] focus-visible:border-gray-200
                                       focus-visible:outline-none";
                    @endphp

                    {{-- Слайд с микро-отступом по бокам для горизонтальных теней --}}
                    <div class="swiper-slide flex justify-center px-[4px]">
                        
                        @if($isReal)
                            {{-- Реальная подкатегория со ссылкой --}}
                            <a href="{{ route('catalog.subcategory', [$cat->parent->parent->slug, $cat->parent->slug, $cat->slug]) }}"
                               class="{{ $cardClasses }}">
                        @else
                            {{-- Фейковая подкатегория --}}
                            <span class="{{ $cardClasses }} cursor-default">
                        @endif
                                {{-- Блок с изображением --}}
                                <div class="w-full h-[90px] mb-[12px] flex items-center justify-center">
                                    <img src="{{ $cat->imageUrl() }}" 
                                         alt="{{ $cat->title }}"
                                         class="w-[125px] h-[80px] object-contain">
                                </div>

                                {{-- Название подкатегории --}}
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

        {{-- 
           ПАГИНАЦИЯ (Полоски): 
           Теперь стоит абсолютно ВНУТРИ зеленого паддинга pb-[16px] родителя.
        --}}
        <div class="js-swiper-pagination rv-pagination !absolute !bottom-[6px] left-0 w-full flex justify-center z-10 mt-0"></div>

    </div>
</div>