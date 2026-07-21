@php
    $recentVariants = \App\Models\ProductVariant::limit(10)->get();
@endphp

@if($recentVariants->count() >= 5)
<div class="max-w-[1484px] overflow-hidden mx-auto relative mt-[70px] mb-[40px]">

    {{-- Заголовок --}}
    <div class="h-[42px] mb-[20px] flex items-center">
        <h2 class="text-[28px] font-bold text-[#231F20]">
            Ранее вы смотрели
        </h2>
    </div>

    <div class="relative px-[16px]">

        {{-- Кнопка назад --}}
        <button id="rv-prev"
                class="absolute left-[1px] top-1/2 -translate-y-1/2
                       w-[32px] h-[32px] rounded-full bg-white border border-gray-200 shadow-md
                       flex items-center justify-center cursor-pointer z-10">
            <span class="text-red-600">
                @include('products.icons.chevron-left-thin')
            </span>
        </button>

        {{-- Лента слайдера --}}
        <div id="rv-track"
             class="slider-track w-full h-[128px] py-[16px] flex overflow-hidden">

            @foreach($recentVariants as $variant)
                <div class="rv-card w-[280.8px] min-h-[96px] p-[8px] flex gap-[8px] border border-gray-200 rounded-lg shadow-md bg-white flex-shrink-0 mr-[12.5px]">
                    <div class="w-[80px] h-[80px] overflow-hidden">
                        <a href="{{ route('catalog.variant', $variant->slug) }}">
                            <img src="{{ $variant->mainImage() }}" alt="{{ $variant->title }}" class="w-full h-full">
                        </a>
                    </div>
                    <div class="text-[13px] text-[#231F20] min-h-[60px] leading-tight line-clamp-3">
                        {{ $variant->title }}
                    </div>
                </div>
            @endforeach

        </div>

        {{-- Кнопка вперед --}}
        <button id="rv-next"
                class="absolute right-[1px] top-1/2 -translate-y-1/2
                       w-[32px] h-[32px] rounded-full bg-white border border-gray-200 shadow-md
                       flex items-center justify-center cursor-pointer z-10">
            <span class="text-red-600">
                @include('products.icons.chevron-right-thin')
            </span>
        </button>

        {{-- Индикатор --}}
        <div id="rv-indicator"
             class="absolute left-1/2 -translate-x-1/2 bottom-[0px] flex gap-[6px]">
            @for($i = 0; $i < 5; $i++)
                <div class="rv-dot w-[32px] h-[2px] rounded bg-[#BDBBBC]"></div>
            @endfor
        </div>

    </div>
</div>

{{-- JS --}}
<script>
document.addEventListener('DOMContentLoaded', () => {

    const track = document.getElementById('rv-track');
    const btnPrev = document.getElementById('rv-prev');
    const btnNext = document.getElementById('rv-next');
     const dots = Array.from(document.querySelectorAll('#rv-indicator .rv-dot')); //  добавлено, иначе метод updateDots() будет падать.
        

    const cards = Array.from(document.querySelectorAll('.rv-card'));
    const total = cards.length;
    const visible = 5;

    // ВАЖНО: реальная ширина карточки + margin-right
    const style = window.getComputedStyle(cards[0]);
    const marginRight = parseFloat(style.marginRight);
    const cardWidth = cards[0].offsetWidth + marginRight;

    // --- КЛОНИРОВАНИЕ ---
    const clonesBefore = cards.slice(-visible).map(c => c.cloneNode(true));
    const clonesAfter = cards.slice(0, visible).map(c => c.cloneNode(true));

    clonesBefore.forEach(c => track.insertBefore(c, track.firstChild));
    clonesAfter.forEach(c => track.appendChild(c));

    // --- НАЧАЛЬНАЯ ПОЗИЦИЯ ---
    let index = visible;
    track.scrollLeft = index * cardWidth;

    // --- ИНДИКАТОР ---
    let dotIndex = 0;
    function updateDots() {
        dots.forEach(d => d.style.backgroundColor = '#BDBBBC');
        dots[dotIndex].style.backgroundColor = '#DC092E';
    }

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
        dotIndex = (dotIndex + 1) % 5;

        scrollToIndex();
        updateDots();
    });

    btnPrev.addEventListener('click', () => {
        if (isAnimating) return;

        index--;
        dotIndex = (dotIndex - 1 + 5) % 5;

        scrollToIndex();
        updateDots();
    });

    updateDots();
});

</script>
@endif
