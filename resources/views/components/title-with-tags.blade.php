{{-- 
    Компонент: Заголовок с текстовым слайдером тегов
    Переменные: 
    - $slider_title: Текст заголовка (напр. 'Хиты продаж')
    - $subcategory_tags: Коллекция подкатегорий (теги)
--}}

@if(isset($slider_title))
<div class="max-w-[1500px] mx-auto h-[56px] flex items-end mb-[8px] px-[6px]">

    {{-- БЛОК 1: ЗАГОЛОВОК --}}
    <div class="pb-[16px] pr-[24px] flex-shrink-0">
        <h2 class="text-[28px] font-bold text-[#231f20] leading-none flex items-center gap-2">
            {{ $slider_title }}
            @if($slider_title == 'Хиты продаж') 
                <span class="text-[24px]">🔥</span> 
            @endif
        </h2>
    </div>

    {{-- БЛОК 2: ТЕКСТОВЫЙ СЛАЙДЕР ТЕГОВ --}}
    <div class="relative flex-1 h-full pl-[18px] pb-[10px] overflow-hidden group">
        
        {{-- Кнопка НАЗАД с градиентным фоном (появляется при прокрутке) --}}
        <div class="js-tag-prev-wrapper absolute left-[18px] top-0 bottom-[10px] w-[60px] z-30 
                    bg-gradient-to-r from-white via-white/80 to-transparent 
                    flex items-center opacity-0 pointer-events-none transition-opacity duration-300">
            <button type="button"
                class="js-tag-prev w-[32px] h-[32px] rounded-full bg-white border border-gray-100
                       shadow-md hover:shadow-lg transition flex items-center justify-center cursor-pointer">
                <span class="text-red-600">@include('products.icons.chevron-left-thin')</span>
            </button>
        </div>

        {{-- Кнопка ВПЕРЕД с градиентным фоном --}}
        <div class="js-tag-next-wrapper absolute right-0 top-0 bottom-[10px] w-[60px] z-30 
                    bg-gradient-to-l from-white via-white/80 to-transparent 
                    flex items-center justify-end transition-opacity duration-300">
            <button type="button"
                class="js-tag-next w-[32px] h-[32px] rounded-full bg-white border border-gray-100
                       shadow-md hover:shadow-lg transition flex items-center justify-center cursor-pointer">
                <span class="text-red-600">@include('products.icons.chevron-right-thin')</span>
            </button>
        </div>

        {{-- ТРЕК СЛАЙДЕРА (Лента тегов) --}}
        <div class="js-tag-track flex items-center overflow-x-auto no-scrollbars scroll-smooth h-full whitespace-nowrap">
            
            {{-- Плитка "Все" (Активная по умолчанию) --}}
            <div class="px-[6px] py-[6px] flex-shrink-0">
                <div class="border border-black bg-white rounded-[5px] px-[12px] py-[5px] 
                            text-[15px] font-semibold text-[#232F20] cursor-pointer transition-all">
                    Все
                </div>
            </div>

            {{-- Рендер тегов подкатегорий --}}
            @foreach($categoriesHit as $tag)
                <div class="px-[6px] py-[6px] flex-shrink-0">
                    {{-- 
                    Добавлены классы для тени при наведении:
                    hover:shadow-[0_4px_12px_rgba(0,0,0,0.12)] - мягкая тень
                    hover:bg-white - меняем фон на белый, чтобы тень была лучше видна
                    hover:border-gray-100 - смягчаем границы
                    --}}
                    <div class="border border-[#F4F4F4] bg-[#F4F4F4] rounded-[5px] px-[12px] py-[5px] 
                                text-[15px] text-[#232F20] cursor-pointer transition-all duration-300
                                hover:bg-white hover:border-gray-100 hover:shadow-[0_4px_12px_rgba(0,0,0,0.12)]">
                        {{ $tag->title }}
                    </div>
                </div>
            @endforeach

        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const track = document.querySelector('.js-tag-track');
    const prevWrapper = document.querySelector('.js-tag-prev-wrapper');
    const nextWrapper = document.querySelector('.js-tag-next-wrapper');
    const btnPrev = document.querySelector('.js-tag-prev');
    const btnNext = document.querySelector('.js-tag-next');

    if (!track || !btnPrev || !btnNext) return;

    const scrollStep = 250; // Шаг прокрутки

    function updateTagButtons() {
        const scrollLeft = track.scrollLeft;
        const maxScroll = track.scrollWidth - track.clientWidth;

        // Логика кнопки НАЗАД
        if (scrollLeft > 5) {
            prevWrapper.classList.remove('opacity-0', 'pointer-events-none');
        } else {
            prevWrapper.classList.add('opacity-0', 'pointer-events-none');
        }

        // Логика кнопки ВПЕРЕД
        if (scrollLeft < maxScroll - 5) {
            nextWrapper.classList.remove('opacity-0', 'pointer-events-none');
        } else {
            nextWrapper.classList.add('opacity-0', 'pointer-events-none');
        }
    }

    btnNext.addEventListener('click', () => {
        track.scrollBy({ left: scrollStep, behavior: 'smooth' });
    });

    btnPrev.addEventListener('click', () => {
        track.scrollBy({ left: -scrollStep, behavior: 'smooth' });
    });

    // Слушаем скролл для обновления видимости кнопок
    track.addEventListener('scroll', updateTagButtons);
    
    // Начальная проверка (если теги не влезают сразу)
    window.addEventListener('resize', updateTagButtons);
    setTimeout(updateTagButtons, 100);
});
</script>
@endif