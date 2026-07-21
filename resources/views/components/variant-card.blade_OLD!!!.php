@props(['variant', 'isFavorite' => false])

<div class="w-[287px] h-[459px] border border-gray-200 rounded-xl p-[12px]
            shadow-md shadow-gray-200/50 hover:shadow-lg hover:shadow-gray-300/60
            backdrop-blur-sm transition bg-white flex flex-col">

    {{-- Верхний блок: лейбл + избранное --}}
    <div class="flex justify-between items-center mb-3">
        <div class="flex gap-2">
            @foreach($product_variant->labels as $label)
                <x-dynamic-component :component="$label->component" />
            @endforeach
        </div>
        <button class="favorite-toggle" data-id="{{ $product->id }}">
            @if($isFavorite)
                @include('products.icons.heart-filled')
            @else
                @include('products.icons.heart-outline')
            @endif
        </button>
    </div>

    {{-- Фото --}}
    <a href="{{ route('products.show', $product->slug) }}" class="mb-3">
        <div class="w-[246px] h-[246px] mx-auto rounded-lg overflow-hidden bg-gray-100">
            <img src="{{ $product->image_url }}"
                 alt="{{ $product->title }}"
                 class="w-full h-full object-cover transition-transform duration-300 hover:scale-105">
        </div>
    </a>

    {{-- Название (фиксируем высоту на 2 строки) --}}
    <a href="{{ route('products.show', $product->slug) }}" class="mb-3">
        <h2 class="text-base font-semibold line-clamp-2 h-[48px]">
            {{ $v->title }}
        </h2>
    </a>

    {{-- Блок цен + кнопка --}}
    <div class="grid grid-cols-2 gap-4 items-start mt-auto">

        {{-- Левая колонка (фиксируем высоту) --}}
        <div class="min-h-[60px] flex flex-col justify-start">
            {{-- Текущая цена --}}
            <div class="text-lg font-bold text-gray-900">
                {{ $product->price }} ₽
            </div>

            {{-- Старая цена + процент скидки --}}
            @if($product->old_price > 0)
                <div class="flex items-center gap-2 mt-1">
                    <span class="text-gray-400 text-sm line-through decoration-gray-400">
                        {{ $product->old_price }} ₽
                    </span>
                    @if($product->discount_percent)
                        <span class="text-red-600 text-sm font-semibold">
                            -{{ $product->discount_percent }}%
                        </span>
                    @endif
                </div>
            @endif
        </div>

        {{-- Кнопка --}}
        <div class="text-right">
            @php
                use App\Models\CartItem;
                use Illuminate\Support\Facades\Auth;
                $inCart = CartItem::where('user_id', Auth::id())
                    ->where('product_id', $product->id)
                    ->exists();
            @endphp

            @if($inCart)
                <a href="{{ route('cart.index') }}"
                class="block text-center bg-white border border-red-600 text-red-600 font-semibold px-2 py-1 rounded-lg transition">
                    В корзине
                </a>
            @else
                <form action="{{ route('cart.add', $product) }}" method="POST" class="mx-auto">
                    @csrf
                    <button class="bg-red-600 hover:bg-red-700 text-white font-semibold px-2 py-1 rounded-lg transition">
                        В корзину
                    </button>
                </form>
            @endif
        </div>
    </div>


    {{-- Рейтинг + отзывы --}}
    <div class="flex items-center gap-4 text-sm text-gray-600">
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
