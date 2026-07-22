@props(['variant', 'isFavorite' => false])

@php
    $product = $variant->product;
@endphp

<div class="w-full bg-white border border-gray-100 rounded-lg
    shadow-[0_2px_8px_rgba(0,0,0,0.20)]
    cursor-pointer transition-all duration-200
    hover:shadow-[0_6px_20px_rgba(0,0,0,0.28)]
    hover:border-gray-200
    focus-visible:shadow-[0_6px_20px_rgba(0,0,0,0.28)]
    focus-visible:border-gray-200
    focus-visible:outline-none
    px-[30px] py-[14px] flex mb-[16px]">

    {{-- Колонка 1: фото --}}
    <div class="w-[327px] min-h-[302px] flex items-center justify-center">
        <div class="w-[260px] h-[260px] rounded-lg overflow-hidden bg-gray-100">
            <a href="{{ route('catalog.variant', $variant->slug) }}">
                <img src="{{ $variant->mainImage() }}"
                     alt="{{ $variant->title }}"
                     class="w-full h-full object-cover">
            </a>
        </div>
    </div>

    {{-- Колонка 2 --}}
    <div class="w-[436px] min-h-[302px] pl-[8px] pr-[65px] flex flex-col">

        {{-- Название --}}
        <a href="{{ route('catalog.variant', $variant->slug) }}" class="block mb-[2px]">
            <div class="w-[364px] h-[20px] text-[15px] font-semibold text-[#231F20] truncate">
                {{ $variant->title }}
            </div>
        </a>

        {{-- Код товара --}}
        <div class="w-[123px] h-[21px] text-[14px] text-gray-600 mb-1">
            Код товара: {{ $variant->id }}
        </div>

        <hr class="border-t border-dashed border-gray-300 mb-2">

        {{-- Описание --}}
        <div class="w-[364px] text-[14px] text-gray-700 pb-[5px] leading-snug line-clamp-2">
            {{ $variant->excerpt }}
        </div>

        <hr class="border-t border-dashed border-gray-300 mt-auto">
    </div>

    {{-- Колонка 3 --}}
    <div class="w-[327px] min-h-[302px] pl-[35px] flex flex-col justify-between">

        {{-- Лейбл + рейтинг --}}
        <div class="flex justify-between items-center mb-3">
            <div class="flex gap-2">
                @foreach($variant->labels as $label)
                    <x-dynamic-component :component="'labels.' . $label->component" />
                @endforeach
            </div>

            <div class="flex items-center gap-2 text-sm text-gray-600">
                <div class="flex items-center gap-1">
                    @include('products.icons.star')
                    <span class="font-semibold text-gray-900">
                        {{ number_format($product->rating, 1) }}
                    </span>
                </div>
                <div>
                    <a href="#" class="flex items-center gap-1 text-gray-500 hover:text-gray-700">
                        @include('products.icons.message')
                        <span>
                            {{ number_format($product->reviews_count, 0, '.', ' ') }}
                            {{ $product->reviews_label }}
                        </span>
                    </a>
                </div>
            </div>
        </div>

        {{-- Цена --}}
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

        {{-- Кнопки --}}
        <div class="grid grid-cols-5 gap-3 mb-3">

            <div class="col-span-4">
                @php
                    use App\Models\CartItem;
                    use Illuminate\Support\Facades\Auth;

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
                    <form action="{{ route('cart.add', $variant) }}" method="POST">
                        @csrf
                        <button class="w-full bg-red-600 hover:bg-red-700 text-white font-semibold py-2 rounded-lg text-sm">
                            В корзину
                        </button>
                    </form>
                @endif
            </div>

            <div class="col-span-1 flex justify-center items-center">
                <button class="favorite-toggle" data-id="{{ $variant->id }}">
                    @if($isFavorite)
                        @include('products.icons.heart-filled')
                    @else
                        @include('products.icons.heart-outline')
                    @endif
                </button>
            </div>
        </div>

        {{-- Наличие --}}
        @if($variant->stock > 0)
            <div class="bg-green-100 text-green-700 text-sm font-semibold px-3 py-2 rounded">
                В наличии
            </div>
        @else
            <div class="bg-red-100 text-red-700 text-sm font-semibold px-3 py-2 rounded">
                Товар закончился
            </div>
        @endif

    </div>

</div>
