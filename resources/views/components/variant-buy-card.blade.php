@props(['variant', 'isFavorite' => false])

@php
    $product = $variant->product;
@endphp

<div class="border border-gray-200 rounded-xl p-5 shadow-md shadow-gray-200/50 bg-white">

    {{-- Верхняя строка: лейбл + рейтинг --}}
    <div class="flex justify-between items-center mb-4">

        {{-- Лейбл (если есть) --}}
        <div>
            @if($variant->label)
                <span class="bg-red-600 text-white text-xs font-semibold px-2 py-1 rounded">
                    {{ $variant->label }}
                </span>
            @endif
        </div>

        {{-- Рейтинг + отзывы --}}
        <div class="flex items-center gap-2 text-sm text-gray-600">
            <div class="flex items-center gap-1">
                @include('products.icons.star')
                <span class="font-semibold text-gray-900">
                    {{ number_format($product->rating, 1) }}
                </span>
            </div>

            <a href="#" class="flex items-center gap-1 text-gray-500 hover:text-gray-700">
                @include('products.icons.message')
                <span>
                    {{ number_format($product->reviews_count, 0, '.', ' ') }}
                    {{ $product->reviews_label }}
                </span>
            </a>
        </div>

    </div>

    {{-- Блок цен --}}
    <div class="grid grid-cols-2 gap-4 items-start mb-6">

        {{-- Текущая цена --}}
        <div>
            <div class="text-4xl font-bold text-gray-900">
                {!! $variant->formattedPrice(40, 31) !!}
            </div>
        </div>

        {{-- Старая цена + процент --}}
        <div class="text-right">
            @if($variant->old_price > 0)
                <div class="text-gray-400 text-lg line-through decoration-gray-400">
                    {!! $variant->formattedOldPrice(18, 18) !!}
                </div>

                @if($variant->discount_percent)
                    <div class="text-red-600 text-[18px] font-semibold">
                        -{{ $variant->discount_percent }}%
                    </div>
                @endif
            @endif
        </div>

    </div>

    {{-- Кнопки: В корзину + избранное --}}
    <div class="grid grid-cols-5 gap-3 mb-6">

        {{-- Кнопка корзины (4/5 ширины) --}}
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
                   class="block text-center bg-white border border-red-600 text-red-600 font-semibold py-3 rounded-lg">
                    В корзине
                </a>
            @else
                <form action="{{ route('cart.add', $variant) }}" method="POST">
                    @csrf
                    <button class="w-full bg-red-600 hover:bg-red-700 text-white font-semibold py-3 rounded-lg">
                        Добавить в корзину
                    </button>
                </form>
            @endif

        </div>

        {{-- Кнопка избранного --}}
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

    {{-- Статус наличия --}}
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
