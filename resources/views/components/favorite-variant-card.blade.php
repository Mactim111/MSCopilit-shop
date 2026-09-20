@props(['variant'])

@php
    $product = $variant->product;
    $cartService = app(App\Services\CartService::class);
    $available = $variant->available_stock;
    $isAvailable = $variant->is_active && $available > 0;
@endphp

<div data-favorite-card
     class="relative w-full bg-white border border-gray-100 rounded-lg
    shadow-[0_2px_8px_rgba(0,0,0,0.20)]
    transition-all duration-200
    hover:shadow-[0_6px_20px_rgba(0,0,0,0.28)]
    hover:border-gray-200
    focus-visible:shadow-[0_6px_20px_rgba(0,0,0,0.28)]
    focus-visible:border-gray-200
    focus-visible:outline-none
    px-[30px] py-[14px] flex mb-[16px]">

    <button type="button"
            class="absolute top-[3px] right-[3px] z-10
                   w-5 h-5 flex items-center justify-center
                   text-[#231F20] hover:text-[#DC092E] transition-colors cursor-pointer"
            data-favorite-remove
            data-favorite-url="{{ route('favorites.toggle', $variant) }}"
            aria-label="Удалить товар из избранного">
        <svg class="w-[22px] h-[22px]" fill="none" stroke="currentColor" viewBox="0 0 24 24"
             aria-hidden="true">
            <path stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
        </svg>
    </button>

    <div class="w-[327px] min-h-[302px] flex items-center justify-center">
        <div class="w-[260px] h-[260px] rounded-lg overflow-hidden bg-gray-100 cursor-pointer">
            <a href="{{ route('catalog.variant', $variant->slug) }}">
                <img src="{{ $variant->mainImage() }}"
                     alt="{{ $variant->title }}"
                     class="w-full h-full object-cover">
            </a>
        </div>
    </div>

    <div class="w-[436px] min-h-[302px] pl-[8px] pr-[65px] flex flex-col">
        <a href="{{ route('catalog.variant', $variant->slug) }}" class="block mb-[2px] cursor-pointer">
            <div class="w-[364px] min-h-[40px] text-[15px] font-bold text-[#231F20]">
                {{ $variant->title }}
            </div>
        </a>

        <div class="h-[21px] text-[14px] text-gray-600 mb-1">
            Код товара: {{ $variant->article }}
        </div>

        <hr class="border-t border-dashed border-gray-300 mb-2">

        <div class="w-[364px] min-h-full text-[14px] text-gray-700 pb-[5px]">
            {!! $variant->formatted_excerpt !!}
            <hr class="border-t border-dashed border-gray-300 mt-[10px]">
        </div>
    </div>

    <div class="w-[327px] min-h-[302px] pl-[35px] flex flex-col justify-between">
        <div class="flex justify-between items-center mb-3 mt-1">
            <div class="flex gap-2">
                @foreach($variant->labels as $label)
                    <x-dynamic-component :component="'labels.' . $label->component" />
                @endforeach
            </div>

            <div class="flex items-center gap-3 text-sm text-gray-600">
                <div class="flex items-center gap-2">
                    @include('products.icons.star')
                    <span class="font-semibold text-gray-400">
                        {{ number_format($product->rating, 1) }}
                    </span>
                </div>
                <div>
                    <a href="#" class="flex items-center gap-1 text-[#007EEF] hover:text-[#0064cc] transition-all duration-200">
                        @include('products.icons.message')
                        <span>
                            {{ number_format($product->reviews_count, 0, '.', ' ') }}
                            {{ $product->reviews_label }}
                        </span>
                    </a>
                </div>
            </div>
        </div>

        <div class="mb-4">
            <div class="text-3xl font-bold text-gray-900">
                {!! $variant->formattedPrice(28, 17) !!}
            </div>

            @if($variant->old_price > 0)
                <div class="flex items-center gap-2 mt-1">
                    <span class="text-gray-400 text-sm line-through decoration-gray-400">
                        {!! $variant->formattedOldPrice(15, 15) !!}
                    </span>

                    @if($variant->discount_percent)
                        <span class="text-red-600 text-sm font-semibold">
                            -{{ $variant->discount_percent }}%
                        </span>
                    @endif
                </div>
            @endif
        </div>

        <div class="grid grid-cols-5 gap-3 mb-3">
            <div class="col-span-4">
                @php($inCart = $cartService->has($variant->id))

                @if(!$isAvailable)
                    <button disabled
                            class="w-full bg-gray-200 text-gray-500 font-semibold py-2 rounded-lg cursor-not-allowed">
                        {{ $variant->is_active ? 'Нет в наличии' : 'Снят с продажи' }}
                    </button>
                @elseif($inCart)
                    <a href="{{ route('cart.index') }}"
                       class="block text-center bg-white border border-red-600 text-red-600 font-semibold py-2 rounded-lg text-[15px] cursor-pointer hover:bg-red-500 hover:text-white">
                        В корзине
                    </a>
                @else
                    <form action="{{ route('cart.add', $variant) }}" method="POST"
                          data-cart-url="{{ route('cart.index') }}"
                          data-cart-link-class="block text-center bg-white border border-red-600 text-red-600 font-semibold py-2 rounded-lg text-[15px] cursor-pointer hover:bg-red-500 hover:text-white"
                          class="js-cart-add-form">
                        @csrf
                        <button class="w-full bg-red-600 hover:bg-red-700 text-white font-semibold py-2 rounded-lg text-[15px] cursor-pointer">
                            В корзину
                        </button>
                    </form>
                @endif
            </div>

            <div class="col-span-1 flex justify-center items-center">
                <button type="button"
                        class="favorite-toggle cursor-pointer"
                        data-favorite-remove
                        data-id="{{ $variant->id }}"
                        data-favorite-url="{{ route('favorites.toggle', $variant) }}">
                    @include('products.icons.heart-filled')
                </button>
            </div>
        </div>

        @if(!$variant->is_active)
            <div class="bg-gray-100 text-gray-600 text-sm font-semibold px-3 py-2 mb-3 rounded">
                Снят с продажи
            </div>
        @elseif($available > 5)
            <div class="bg-green-100 text-green-700 text-sm font-semibold px-3 mb-3 py-2 rounded">
                В наличии
            </div>
        @elseif($available > 0)
            <div class="bg-blue-100 text-blue-700 text-sm font-semibold px-3 py-2 mb-3 rounded">
                Товар заканчивается
            </div>
        @else
            <div class="bg-red-100 text-red-700 text-sm font-semibold px-3 py-2 mb-3 rounded">
                Товар закончился
            </div>
        @endif
    </div>
</div>
