@php
    $cartService = app(\App\Services\CartService::class);
    $inCart = $cartService->has($variant->id);
@endphp

<div data-product-card="compact"
     class="flex min-h-[80px] w-full items-stretch gap-[11px] {{ $initialTab === 'about' ? 'hidden' : '' }}">
    <div class="w-[80px] h-[80px] shrink-0 rounded-lg overflow-hidden bg-gray-100">
        <a href="{{ route('catalog.variant', $variant->slug) }}">
            <img src="{{ $variant->mainImage() }}"
                 alt="{{ $variant->title }}"
                 class="w-full h-full object-cover">
        </a>
    </div>

    <div class="min-w-0 flex-1 flex flex-col text-left">
        @php
            $displayTitle = str_replace('-', '&#8209;', e($variant->title));
        @endphp

        <h1 class="text-[28px] leading-tight font-bold text-black mb-[14px] items-center">
            {!! $initialTab === 'reviews' ? 'Отзывы на ' : '' !!}{!! $displayTitle !!}
        </h1>
        <p class="text-[18px] leading-tight text-black justify-start items-center">
            Код товара: {{ $variant->article }}
        </p>
    </div>

    <div class="grid min-w-[450px] shrink-0 grid-cols-2 items-center gap-[11px] pl-[11px]">
        <div class="min-w-[172px] min-w-[80px] flex flex-col justify-start">
            <div class="text-[40px] font-bold text-[#231F20]">
                {!! $variant->formattedPrice(40, 33) !!}
            </div>

            @if($variant->old_price > 0)
                <div class="flex items-center gap-1">
                    <span class="text-[18px] font-semibold text-gray-400 line-through decoration-gray-500">
                        {!! $variant->formattedOldPrice(18, 18) !!}
                    </span>
                    @if($variant->discount_percent)
                        <span class="text-[18px] font-bold text-red-600">
                            -{{ $variant->discount_percent }}%
                        </span>
                    @endif
                </div>
            @endif
        </div>

        @if($inCart)
            <a href="{{ route('cart.index') }}"
               class="flex h-[50px] w-full items-center justify-center rounded-lg border border-red-600 bg-white text-[20px] font-semibold text-red-600 hover:bg-red-500 hover:text-white">
                В корзине
            </a>
        @else
            <div class="flex h-full w-full items-center justify-center">
                <form action="{{ route('cart.add', $variant) }}"
                    method="POST"
                    data-cart-url="{{ route('cart.index') }}"
                    data-cart-link-class="flex items-center justify-center w-full h-[50px] bg-white border border-red-600 text-red-600 font-semibold rounded-lg hover:bg-red-500 hover:text-white text-[20px]"
                    class="w-full js-cart-add-form">
                    @csrf
                    <button type="submit"
                            class="h-[50px] w-full rounded-lg bg-red-600 text-[20px] font-semibold text-white transition-colors hover:bg-red-700">
                        В корзину
                    </button>
                </form>
            </div>
        @endif
    </div>
</div>
