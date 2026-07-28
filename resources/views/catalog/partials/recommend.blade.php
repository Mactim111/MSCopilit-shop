@php
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
        <button id="rec-prev"
            class="absolute left-[1px] top-1/2 -translate-y-1/2
                       w-[32px] h-[32px] rounded-full bg-white border border-gray-200 shadow-md
                       flex items-center justify-center cursor-pointer z-10">
            <span class="text-red-600">
                @include('products.icons.chevron-left-thin')
            </span>
        </button>

        {{-- Лента --}}
        <div id="rec-track"
            class="slider-track w-full h-[491px] flex py-[16px] overflow-x-auto">

            @foreach($recommendVariants as $variant)
            <div class="rec-card w-[287px] min-h-[459px] border border-gray-200 rounded-lg p-[12px]
                            shadow-md shadow-gray-200/50 bg-white flex-shrink-0 mr-[8px] flex flex-col">

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

                    <div class="text-right">
                        <form action="{{ route('cart.add', $variant) }}" method="POST" class="mx-auto">
                            @csrf
                            <button class="bg-red-600 hover:bg-red-700 text-white font-semibold px-2 py-1 rounded-lg transition">
                                В корзину
                            </button>
                        </form>
                    </div>
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
            @endforeach

        </div>

        {{-- Кнопка вперед --}}
        <button id="rec-next"
            class="absolute right-[1px] top-1/2 -translate-y-1/2
                       w-[32px] h-[32px] rounded-full bg-white border border-gray-200 shadow-md
                       flex items-center justify-center cursor-pointer z-10">
            <span class="text-red-600">
                @include('products.icons.chevron-right-thin')
            </span>
        </button>

    </div>
</div>

{{-- JS --}}
<script>
    document.addEventListener('DOMContentLoaded', () => {

        const track = document.getElementById('rec-track');
        const btnPrev = document.getElementById('rec-prev');
        const btnNext = document.getElementById('rec-next');

        const cards = Array.from(document.querySelectorAll('.rec-card'));
        const total = cards.length;
        const visible = 5;

        const style = window.getComputedStyle(cards[0]);
        const marginRight = parseFloat(style.marginRight);
        const cardWidth = cards[0].offsetWidth + marginRight;

        const clonesBefore = cards.slice(-visible).map(c => c.cloneNode(true));
        const clonesAfter = cards.slice(0, visible).map(c => c.cloneNode(true));

        clonesBefore.forEach(c => track.insertBefore(c, track.firstChild));
        clonesAfter.forEach(c => track.appendChild(c));

        let index = visible;
        track.scrollLeft = index * cardWidth;

        let isAnimating = false;

        function scrollToIndex() {
            isAnimating = true;
            track.scrollTo({
                left: index * cardWidth,
                behavior: 'smooth'
            });
        }

        track.addEventListener('scroll', () => {
            if (!isAnimating) return;

            const target = index * cardWidth;
            const diff = Math.abs(track.scrollLeft - target);

            if (diff < 1) {
                isAnimating = false;

                if (index < visible) {
                    index += total;
                    track.scrollLeft = index * cardWidth;
                }

                if (index >= total + visible) {
                    index -= total;
                    track.scrollLeft = index * cardWidth;
                }
            }
        });

        btnNext.addEventListener('click', () => {
            if (isAnimating) return;
            index++;
            scrollToIndex();
        });

        btnPrev.addEventListener('click', () => {
            if (isAnimating) return;
            index--;
            scrollToIndex();
        });
    });
</script>
@endif