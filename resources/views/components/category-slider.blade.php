{{-- 
    Компонент: category-slider
    Назначение: вывод подкатегорий третьего уровня в виде горизонтального слайдера,
    как у 5 Элемент.

    Логика:
    - подкатегории третьего уровня = children() от категорий второго уровня
    - сначала выводим подкатегории, у которых есть товары (кликабельные)
    - затем подкатегории без товаров (фейковые, но выглядят как ссылки)
    - кнопки навигации не резервируют место
    - мягкая тень ПОД слайдером (усиленная, как у 5 Элемент)
--}}

<div class="w-full bg-white border-b border-gray-200">

    <div class="max-w-[1500px] mx-auto h-[42px] relative">

        {{-- Усиленная тень ПОД слайдером (как у 5 Элемент, но чуть мощнее) --}}
        <div class="absolute bottom-0 left-0 w-full h-[18px] pointer-events-none z-0"
             style="
                box-shadow:
                    0px 10px 14px -6px rgba(0,0,0,0.22),
                    0px 4px 8px -4px rgba(0,0,0,0.18);
             ">
        </div>

        {{-- Кнопка назад --}}
        <button type="button"
            class="js-cat-prev absolute left-[4px] top-1/2 -translate-y-1/2
                   w-[32px] h-[32px] rounded-full bg-white border border-gray-200 shadow-md
                   flex items-center justify-center cursor-pointer z-20 opacity-0 pointer-events-none transition">
            <span class="text-red-600">@include('products.icons.chevron-left-thin')</span>
        </button>

        {{-- Лента подкатегорий --}}
        <div class="js-cat-track flex items-center gap-6 overflow-hidden whitespace-nowrap
                    pl-[4px] pr-[40px] h-full relative z-10">

            {{-- Акции --}}
            <a href="/sales"
               class="text-red-600 font-semibold hover:text-red-700 text-[14px]">
                Все акции
            </a>

            {{-- Подкатегории --}}
            @foreach($categoriesHit as $cat)

                @if($cat->products_count > 0)
                    {{-- Реальная подкатегория: есть товары --}}
                    <a href="{{ route('catalog.category', [$cat->parent->parent->slug, $cat->parent->slug, $cat->slug]) }}"
                       class="text-[#231F20] hover:text-red-600 transition text-[14px] font-semibold">
                        {{ $cat->title }}
                    </a>
                @else
                    {{-- Фейковая подкатегория: нет товаров --}}
                    <span class="text-[#231F20] hover:text-red-600 transition text-[14px] font-semibold cursor-pointer">
                        {{ $cat->title }}
                    </span>
                @endif

            @endforeach

        </div>

        {{-- Кнопка вперед --}}
        <button type="button"
            class="js-cat-next absolute right-[4px] top-1/2 -translate-y-1/2
                   w-[32px] h-[32px] rounded-full bg-white border border-gray-200 shadow-md
                   flex items-center justify-center cursor-pointer z-20">
            <span class="text-red-600">@include('products.icons.chevron-right-thin')</span>
        </button>

    </div>
</div>

{{-- JS слайдера --}}
<script>
document.addEventListener('DOMContentLoaded', () => {

    const track = document.querySelector('.js-cat-track');
    const btnPrev = document.querySelector('.js-cat-prev');
    const btnNext = document.querySelector('.js-cat-next');

    if (!track || !btnPrev || !btnNext) return;

    let position = 0;
    const step = 180; // шаг прокрутки

    function updateButtons() {
        const maxScroll = track.scrollWidth - track.clientWidth;

        // Назад
        if (position <= 0) {
            btnPrev.style.opacity = '0';
            btnPrev.style.pointerEvents = 'none';
        } else {
            btnPrev.style.opacity = '1';
            btnPrev.style.pointerEvents = 'auto';
        }

        // Вперёд
        if (position >= maxScroll - 1) {
            btnNext.style.opacity = '0';
            btnNext.style.pointerEvents = 'none';
        } else {
            btnNext.style.opacity = '1';
            btnNext.style.pointerEvents = 'auto';
        }
    }

    btnPrev.addEventListener('click', () => {
        position = Math.max(position - step, 0);
        track.scrollTo({ left: position, behavior: 'smooth' });
        updateButtons();
    });

    btnNext.addEventListener('click', () => {
        const maxScroll = track.scrollWidth - track.clientWidth;
        position = Math.min(position + step, maxScroll);
        track.scrollTo({ left: position, behavior: 'smooth' });
        updateButtons();
    });

    // Если всё помещается — скрываем обе кнопки
    const maxScrollInitial = track.scrollWidth - track.clientWidth;
    if (maxScrollInitial <= 0) {
        btnPrev.style.opacity = '0';
        btnPrev.style.pointerEvents = 'none';
        btnNext.style.opacity = '0';
        btnNext.style.pointerEvents = 'none';
    } else {
        updateButtons();
    }
});
</script>
