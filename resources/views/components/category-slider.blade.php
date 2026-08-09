{{-- 
    Компонент: category-slider
    Назначение: вывод подкатегорий третьего уровня в виде горизонтального слайдера,
    как у 5 Элемент.

    Логика:
    - подкатегории третьего уровня = children() от категорий второго уровня
    - сначала выводим подкатегории, у которых есть товары (кликабельные)
    - затем подкатегории без товаров (фейковые, но выглядят как ссылки)
    - кнопки навигации НЕ резервируют место внутри слайдера
    - кнопки вынесены за пределы контейнера 1500px (как у 5 Элемент)
    - кнопки абсолютные, контейнер relative — поэтому они могут выходить наружу
    - JS работает по селекторам, поэтому структура DOM не влияет на работу
    - мягкая тень ПОД слайдером (усиленная, как у 5 Элемент)
--}}

<div class="w-full bg-white">

    {{-- Внешний контейнер 1500px — кнопки будут его прямыми детьми --}}
    <div class="max-w-[1500px] mx-auto relative h-[42px]">

        {{-- Кнопка назад (вынесена за пределы контейнера) --}}
        <button type="button"
            class="js-cat-prev absolute left-[-16px] top-1/2 -translate-y-1/2
                   w-[32px] h-[32px] rounded-full bg-white border border-gray-100
                   shadow-md hover:shadow-lg transition
                   flex items-center justify-center cursor-pointer z-20 opacity-0 pointer-events-none">
            <span class="text-red-600">@include('products.icons.chevron-left-thin')</span>
        </button>

        {{-- Лента подкатегорий (внутри контейнера, кнопки не занимают место) --}}
        <div class="js-cat-track flex items-center gap-6 overflow-hidden whitespace-nowrap
                    pl-[4px] pr-[4px] h-full relative z-10">

            {{-- Акции --}}
            <a href="/sales"
               class="text-red-600 font-semibold hover:text-red-700 text-[14px]">
                Все акции
            </a>

            {{-- Подкатегории --}}
            @foreach($categoriesHit as $cat)

                @if($cat->products_count > 0)
                    {{-- Реальная подкатегория: есть товары --}}
                    <a href="{{ route('catalog.subcategory', [$cat->parent->parent->slug, $cat->parent->slug, $cat->slug]) }}"
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

        {{-- Кнопка вперед (вынесена за пределы контейнера) --}}
        <button type="button"
            class="js-cat-next absolute right-[-16px] top-1/2 -translate-y-1/2
                   w-[32px] h-[32px] rounded-full bg-white border border-gray-100
                   shadow-md hover:shadow-lg transition
                   flex items-center justify-center cursor-pointer z-20">
            <span class="text-red-600">@include('products.icons.chevron-right-thin')</span>
        </button>

    </div>
</div>

{{-- JS слайдера --}}
<script>
document.addEventListener('DOMContentLoaded', () => {

    // Важно:
    // Кнопки вынесены за пределы контейнера 1500px.
    // JS ищет их по селекторам, поэтому структура DOM не влияет на работу.

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
