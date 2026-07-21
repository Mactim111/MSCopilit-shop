@props(['variant', 'isFavorite' => false])

@php
    $product = $variant->product;
@endphp

<div class="w-[287px] h-[459px] border border-gray-200 rounded-xl p-[12px]
            shadow-md shadow-gray-200/50 hover:shadow-lg hover:shadow-gray-300/60
            backdrop-blur-sm transition bg-white flex flex-col">

    {{-- Верхний блок: лейблы + избранное --}}
    <div class="flex justify-between items-center mb-3">
        <div class="flex gap-2">
            @foreach($variant->labels as $label)
                <x-dynamic-component :component="'labels.' . $label->component" />
            @endforeach
        </div>

        <button class="favorite-toggle" data-id="{{ $variant->id }}">
            @if($isFavorite)
                @include('products.icons.heart-filled')
            @else
                @include('products.icons.heart-outline')
            @endif
        </button>
    </div>

    {{-- Фото --}}
    <a href="{{ route('catalog.variant', $variant->slug) }}" class="mb-3">
        <div class="w-[246px] h-[246px] mx-auto rounded-lg overflow-hidden bg-gray-100">
            <img src="{{ $variant->mainImage() }}"
                 alt="{{ $variant->title }}"
                 class="w-full h-full object-cover transition-transform duration-300 hover:scale-105">
        </div>
    </a>

    {{-- Название --}}
    <a href="{{ route('catalog.variant', $variant->slug) }}" class="mb-3">
        <h2 class="text-base font-semibold line-clamp-2 h-[48px] text-[15px]">
            {{ $variant->title }}
        </h2>
    </a>

    {{-- Цена + кнопка --}}
    <div class="grid grid-cols-2 gap-4 items-start mt-auto">

        {{-- Цена --}}
        <div class="min-h-[60px] flex flex-col justify-start">
            <div class="font-bold text-gray-900">
                {!! $variant->formattedPrice(30, 19) !!}
            </div>

            @if($variant->old_price > $variant->price)
                <div class="flex items-center gap-2 mt-1">
                    <span class="text-gray-400 line-through decoration-gray-400 text-[15px]">
                        {!! $variant->formattedOldPrice(28, 17) !!}
                    </span>

                    @if($variant->discountPercent())
                        <span class="text-red-600 font-semibold text-[15px]">
                            -{{ $variant->discountPercent() }}%
                        </span>
                    @endif
                </div>
            @endif
        </div>

        {{-- Кнопка --}}
        <div class="w-full flex items-center justify-center">
            @php
                use App\Models\CartItem;
                use Illuminate\Support\Facades\Auth;

                $inCart = CartItem::where('user_id', Auth::id())
                    ->where('product_variant_id', $variant->id)
                    ->exists();
            @endphp

            @if($inCart)
                <a href="{{ route('cart.index') }}"
                   class="w-[122px] h-[40px] block text-center bg-white border border-red-600 text-red-600 font-semibold px-2 py-1 rounded-lg transition">
                    В корзине
                </a>
            @else
                <form action="{{ route('cart.add', $variant->id) }}" method="POST">
                    @csrf
                    <button class="w-[122px] h-[40px] text-center bg-red-600 hover:bg-red-700 text-white font-semibold px-2 py-1 rounded-lg transition">
                        В корзину
                    </button>
                </form>
            @endif
        </div>
    </div>

    {{-- Рейтинг --}}
    <div class="flex items-center gap-4 text-sm text-gray-600">
        <div class="flex items-center gap-1">
            @include('products.icons.star')
            <span class="font-semibold text-gray-900">
                {{ number_format($product->rating, 1) }}
            </span>
        </div>

        <a href="{{ route('catalog.variant', $variant->slug) }}"
           class="flex items-center gap-1 text-gray-500 hover:text-gray-700">
            @include('products.icons.message')
            <span>
                {{ number_format($product->reviews_count, 0, '.', ' ') }}
                {{ $product->reviews_label }}
            </span>
        </a>
    </div>
</div>
