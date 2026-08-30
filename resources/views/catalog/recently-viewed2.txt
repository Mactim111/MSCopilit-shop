@php
    $recentVariants = \App\Models\ProductVariant::limit(10)->get();
@endphp

@if($recentVariants->count() >= 5)
<div class="max-w-[1500px] overflow-hidden mx-auto relative mt-[70px] mb-[40px]">

    {{-- Заголовок --}}
    <div class="h-[42px] mb-[20px] flex items-center">
        <h2 class="text-[28px] font-bold text-[#231F20]">
            Ранее вы смотрели
        </h2>
    </div>

    <div class="relative px-[16px]">

        {{-- Кнопка назад --}}
        <button 
            class="js-swiper-prev absolute left-[1px] top-[calc(50%-8px)] -translate-y-1/2
                w-[32px] h-[32px] rounded-full bg-white border border-gray-200 shadow-md
                flex items-center justify-center cursor-pointer z-10">
            <span class="text-red-600">@include('products.icons.chevron-left-thin')</span>
        </button>

        {{-- SWIPER --}}
        <div class="swiper js-swiper"
             data-grab="false"
             data-loop="true"
             data-pagination="true"
             data-navigation="true"
             data-space="8.5">

            <div class="swiper-wrapper py-[16px] pb-[25px]">

                @foreach($recentVariants as $variant)
                    <div class="swiper-slide !w-auto px-[3px]">

                        <a href="{{ route('catalog.variant', $variant->slug) }}"
                           class="rv-card w-[280.8px] min-h-[96px] p-[8px] flex gap-[8px] flex-shrink-0
                                border border-gray-100 rounded-lg bg-white 
                                shadow-[0_2px_8px_rgba(0,0,0,0.20)]
                                cursor-pointer transition-all duration-200
                                hover:shadow-[0_6px_20px_rgba(0,0,0,0.28)]
                                hover:border-gray-200
                                focus-visible:shadow-[0_6px_20px_rgba(0,0,0,0.28)]
                                focus-visible:border-gray-200
                                focus-visible:outline-none">

                            <div class="w-[80px] h-[80px] overflow-hidden flex-shrink-0 ">
                                <img src="{{ $variant->mainImage() }}"
                                     alt="{{ $variant->title }}"
                                     class="w-full h-full object-cover">
                            </div>

                            <div class="text-[13px] text-[#231F20] min-h-[60px] leading-tight line-clamp-3">
                                {{ $variant->title }}
                            </div>

                        </a>

                    </div>
                @endforeach

            </div>
        </div>

        {{-- Кастомная полосочная пагинация --}}
        <div class="swiper-pagination rv-pagination js-swiper-pagination"></div>

        {{-- Кнопка вперед --}}
        <button 
            class="js-swiper-next absolute right-[1px] top-[calc(50%-8px)] -translate-y-1/2
                w-[32px] h-[32px] rounded-full bg-white border border-gray-200 shadow-md
                flex items-center justify-center cursor-pointer z-10">
            <span class="text-red-600">@include('products.icons.chevron-right-thin')</span>
        </button>

    </div>
</div>
@endif
