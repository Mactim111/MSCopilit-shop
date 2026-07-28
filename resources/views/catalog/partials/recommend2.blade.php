@php
use App\Models\CartItem;
use Illuminate\Support\Facades\Auth;
$recommendVariants = \App\Models\ProductVariant::limit(10)->get();
@endphp

@if($recommendVariants->count() >= 5)
<div class="max-w-[1499px] overflow-hidden mx-auto relative mt-[70px] mb-[40px]">

    {{-- Заголовок --}}
    <div class="h-[42px] mb-[20px] flex items-center">
        <h2 class="text-[28px] font-bold text-[#231F20]">
            Покупают вместе
        </h2>
    </div>

    <div class="relative px-[16px]">

        {{-- Кнопка назад --}}
        <button
            class="js-swiper-prev absolute left-[1px] top-1/2 -translate-y-1/2
                   w-[32px] h-[32px] rounded-full bg-white border border-gray-200 shadow-md
                   flex items-center justify-center cursor-pointer z-10">
            <span class="text-red-600">
                @include('products.icons.chevron-left-thin')
            </span>
        </button>

        {{-- SWIPER --}}
        <div class="swiper js-swiper"
            data-grab="true"
            data-loop="true"
            data-pagination="false"
            data-navigation="true"
            data-space="8"
            data-slides="5"
            data-breakpoints='{
                 "320": {"slidesPerView": 1.2},
                 "480": {"slidesPerView": 2},
                 "768": {"slidesPerView": 3},
                 "1024": {"slidesPerView": 4},
                 "1280": {"slidesPerView": 5}
             }'>

            <div class="swiper-wrapper">

                @foreach($recommendVariants as $variant)
                <div class="swiper-slide !w-[287px] min-h-[471px] py-[6px]">

                    <div class="rec-card w-[287px] min-h-[459px] flex-shrink-0 mr-[8px] flex flex-col p-[12px]
                        bg-white border border-gray-100 rounded-lg
                        shadow-[0_2px_8px_rgba(0,0,0,0.20)]
                        cursor-pointer transition-all duration-200
                        hover:shadow-[0_6px_20px_rgba(0,0,0,0.28)]
                        hover:border-gray-200
                        focus-visible:shadow-[0_6px_20px_rgba(0,0,0,0.28)]
                        focus-visible:border-gray-200
                        focus-visible:outline-none
                    ">

                        {{-- Верхний блок --}}
                        <div class="flex justify-between items-center mb-3">
                            <div class="flex gap-2">
                                @foreach($variant->labels as $label)
                                <x-dynamic-component :component="'labels.' . $label->component" />
                                @endforeach
                            </div>
                            <button class="favorite-toggle" data-id="{{ $variant->id }}">
                                @include('products.icons.heart-outline')
                            </button>
                        </div>

                        {{-- Фото --}}
                        <a href="{{ route('catalog.variant', $variant->slug) }}" class="mb-3">
                            <div class="w-[240px] h-[240px] mx-auto rounded-lg overflow-hidden bg-gray-100">
                                <img src="{{ $variant->mainImage() }}"
                                    alt="{{ $variant->title }}"
                                    class="w-full h-full object-cover transition-transform duration-300 hover:scale-105">
                            </div>
                        </a>

                        {{-- Название --}}
                        <a href="{{ route('catalog.variant', $variant->slug) }}" class="mb-3">
                            <h2 class="text-base font-semibold line-clamp-2 h-[48px]">
                                {{ $variant->title }}
                            </h2>
                        </a>

                        {{-- Цена + кнопка --}}
                        <div class="grid grid-cols-2 gap-4 items-start">

                            <div class="min-h-[50px] flex flex-col justify-start">
                                <div class="text-lg font-bold text-gray-900">
                                    {!! $variant->formattedPrice(24, 15) !!}
                                </div>

                                @if($variant->old_price > 0)
                                <div class="flex items-center gap-2 mt-1">
                                    <span class="text-gray-400 text-sm line-through decoration-gray-400">
                                        {!! $variant->formattedOldPrice(20, 12) !!}
                                    </span>
                                    @if($variant->discount_percent)
                                    <span class="text-red-600 text-sm font-semibold">
                                        -{{ $variant->discount_percent }}%
                                    </span>
                                    @endif
                                </div>
                                @endif
                            </div>

                            @php
                            $inCart = CartItem::where('user_id', Auth::id())
                            ->where('product_variant_id', $variant->id)
                            ->exists();
                            @endphp

                            @if($inCart)
                            <a href="{{ route('cart.index') }}"
                                class="block text-center bg-white border border-red-600 text-red-600 font-semibold py-2 rounded-lg text-sm">
                                В корзине
                            </a>
                            @else
                            <div class="text-right">
                                <form action="{{ route('cart.add', $variant) }}" method="POST" class="mx-auto">
                                    @csrf
                                    <button class="bg-red-600 hover:bg-red-700 text-white font-semibold px-2 py-1 rounded-lg transition">
                                        В корзину
                                    </button>
                                </form>
                            </div>
                            @endif

                        </div>

                        {{-- Рейтинг --}}
                        <div class="flex items-center gap-4 text-sm text-gray-600 mt-3">
                            <div class="flex items-center gap-1">
                                @include('products.icons.star')
                                <span class="font-semibold text-gray-900">
                                    {{ number_format($variant->product->rating, 1) }}
                                </span>
                            </div>
                            <a href="#" class="flex items-center gap-1 text-gray-500 hover:text-gray-700">
                                @include('products.icons.message')
                                <span>
                                    {{ number_format($variant->product->reviews_count, 0, '.', ' ') }}
                                    {{ $variant->product->reviews_label }}
                                </span>
                            </a>
                        </div>

                    </div>

                </div>
                @endforeach

            </div>
        </div>

        {{-- Кнопка вперед --}}
        <button
            class="js-swiper-next absolute right-[1px] top-1/2 -translate-y-1/2
                   w-[32px] h-[32px] rounded-full bg-white border border-gray-200 shadow-md
                   flex items-center justify-center cursor-pointer z-10">
            <span class="text-red-600">
                @include('products.icons.chevron-right-thin')
            </span>
        </button>

    </div>
</div>
@endif