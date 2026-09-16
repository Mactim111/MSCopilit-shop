@props(['variant', 'isFavorite' => false])

@php
    // Используем наш новый геттер из модели
    $available = $variant->available_stock;
    $isAvailable = $available > 0;
@endphp

<div class="rounded-xl p-5 border border-gray-100 shadow-[0_2px_8px_rgba(0,0,0,0.1)] bg-white">

    {{-- Верхняя строка: лейбл + рейтинг --}}
    <div class="flex justify-between items-center max-h-[40px] mb-2">
        <div class="flex gap-2">
            @foreach($variant->labels as $label)
                <x-dynamic-component :component="'labels.' . $label->component" />
            @endforeach
        </div>

        <div class="flex items-center gap-2 text-sm text-gray-600">
            <div class="flex items-center gap-1">
                @include('products.icons.star')
                <span class="font-semibold text-gray-900">{{ number_format($variant->product->rating, 1) }}</span>
            </div>
        </div>
    </div>

    {{-- Блок цен --}}
    <div class="grid grid-cols-2 gap-4 items-start mb-2">
        <div>
            <div class="text-4xl font-bold text-gray-900">
                {!! $variant->formattedPrice(40, 31) !!}
            </div>
        </div>
        <div class="text-right">
            @if($variant->old_price > 0)
                <div class="text-gray-400 text-lg line-through decoration-gray-400">
                    {!! $variant->formattedOldPrice(18, 18) !!}
                </div>
                @if($variant->discount_percent)
                    <div class="text-red-600 text-[18px] font-semibold">-{{ $variant->discount_percent }}%</div>
                @endif
            @endif
        </div>
    </div>

    {{-- Кнопки: В корзину + избранное --}}
    <div class="grid grid-cols-5 gap-3 mb-6">
        <div class="col-span-4">
            @php
                use App\Services\CartService;
                $inCart = app(CartService::class)->has($variant->id);
            @endphp

            @if(!$isAvailable)
                {{-- Кнопка неактивна, если товара нет --}}
                <button disabled class="w-full bg-gray-200 text-gray-500 font-semibold py-3 rounded-lg cursor-not-allowed">
                    Нет в наличии
                </button>
            @elseif($inCart)
                <a href="{{ route('cart.index') }}"
                   class="block text-center bg-white border border-red-600 text-red-600 font-semibold py-3 rounded-lg hover:bg-red-50 transition">
                    В корзине
                </a>
            @else
                {{-- Передаём модель, чтобы route model binding использовал её slug,
                     указанный в ProductVariant::getRouteKeyName(). --}}
                <form action="{{ route('cart.add', $variant) }}" method="POST"
                      data-cart-url="{{ route('cart.index') }}" class="js-cart-add-form">
                    @csrf
                    <button class="w-full bg-red-600 hover:bg-red-700 text-white font-semibold py-3 rounded-lg transition">
                        Добавить в корзину
                    </button>
                </form>
            @endif
        </div>

        <div class="col-span-1 flex justify-center items-center">
            <button class="favorite-toggle" data-id="{{ $variant->id }}">
                @if($isFavorite) @include('products.icons.heart-filled') @else @include('products.icons.heart-outline') @endif
            </button>
        </div>
    </div>

    {{-- Статус наличия (обновленная логика) --}}
    @if($available > 5)
        <div class="bg-green-100 text-green-700 text-sm font-semibold px-3 py-2 rounded">В наличии</div>
    @elseif($available > 0)
        <div class="bg-blue-100 text-blue-700 text-sm font-semibold px-3 py-2 rounded">Товар заканчивается</div>
    @else
        <div class="bg-red-100 text-red-700 text-sm font-semibold px-3 py-2 rounded">Товар закончился</div>
    @endif

</div>