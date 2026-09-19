{{-- 
    Компонент: title-with-tags
    Универсальный заголовок с опциональным слайдером тегов.
--}}

@php
    $tagContainerClass = $tagContainerClass ?? 'max-w-[1500px] mx-auto';
    $favoritesMode = $favoritesMode ?? false;
    $activeTagId = $activeTagId ?? null;
@endphp

<div class="{{ $tagContainerClass }} h-[56px] flex items-end mb-[8px] pr-[6px]">

    {{-- БЛОК 1: ЗАГОЛОВОК (Отображается, если передан title) --}}
    @if(isset($slider_title))
        <div class="pb-[16px] pr-[24px] flex-shrink-0">
            <h2 class="text-[28px] font-bold text-[#231f20] leading-none flex items-center gap-2">
                {{ $slider_title }}
                @if($slider_title == 'Хиты продаж') 
                    <span class="text-[24px]">🔥</span> 
                @endif
            </h2>
        </div>
    @endif

    {{-- БЛОК 2: ТЕКСТОВЫЙ СЛАЙДЕР ТЕГОВ (Отображается, если передана переменная slider_tags) --}}
    @if(isset($slider_tags))
        <div class="js-tag-container relative flex-1 h-full pl-[18px] pb-[10px] overflow-hidden group">
            
            {{-- Кнопка НАЗАД --}}
            <div class="js-tag-prev-wrapper absolute left-[18px] top-0 bottom-[10px] w-[60px] z-30 
                        bg-gradient-to-r from-white via-white/80 to-transparent 
                        flex items-center opacity-0 pointer-events-none transition-opacity duration-300">
                <button type="button"
                    class="js-tag-prev w-[32px] h-[32px] rounded-full bg-white border border-gray-100
                        shadow-md hover:shadow-lg transition flex items-center justify-center cursor-pointer">
                    <span class="text-red-600">@include('products.icons.chevron-left-thin')</span>
                </button>
            </div>

            {{-- Кнопка ВПЕРЕД --}}
            <div class="js-tag-next-wrapper absolute right-0 top-0 bottom-[10px] w-[60px] z-30 opacity-0 pointer-events-none
                        bg-gradient-to-l from-white via-white/80 to-transparent 
                        flex items-center justify-end transition-opacity duration-300">
                <button type="button"
                    class="js-tag-next w-[32px] h-[32px] rounded-full bg-white border border-gray-100
                        shadow-md hover:shadow-lg transition flex items-center justify-center cursor-pointer">
                    <span class="text-red-600">@include('products.icons.chevron-right-thin')</span>
                </button>
            </div>

            {{-- ТРЕК СЛАЙДЕРА --}}
            <div id="{{ $favoritesMode ? 'favorites-tags' : '' }}"
                 class="js-tag-track flex items-center overflow-x-auto no-scrollbars scroll-smooth h-full whitespace-nowrap">
                
                <div class="px-[6px] py-[6px] flex-shrink-0 relative" data-favorite-tag="all">
                    <div class="border {{ !$favoritesMode || !$activeTagId ? 'border-black bg-white' : 'border-[#F4F4F4] bg-[#F4F4F4]' }} rounded-[5px] px-[12px] py-[5px]
                                text-[15px] font-semibold text-[#232F20] cursor-pointer transition-all"
                         data-favorite-category="all">
                        {{ $favoritesMode ? 'Все товары' : 'Все' }}
                        @if($favoritesMode)
                            <span class="ml-[4px] text-[14px] font-normal text-[#8c8c8c]">{{ $favoriteTotal ?? 0 }}</span>
                            <button type="button"
                                    class="ml-[16px] inline-flex items-center text-[#DC092E] hover:text-[#a80723] transition-colors align-middle cursor-pointer"
                                    data-favorite-category-remove
                                    data-category-title="Все товары"
                                    data-delete-url="{{ route('favorites.delete-all') }}"
                                    aria-label="Удалить все товары из избранного">
                                <svg class="w-[24px] h-[24px]" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                     aria-hidden="true">
                                    <path stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        @endif
                    </div>
                </div>

                @foreach($slider_tags as $tag)
                    <div class="px-[6px] py-[6px] flex-shrink-0 relative" data-favorite-tag="{{ $tag->id }}">
                        <div class="border {{ $favoritesMode && (int) $activeTagId === (int) $tag->id ? 'border-black bg-white' : 'border-[#F4F4F4] bg-[#F4F4F4]' }} rounded-[5px] px-[12px] py-[5px]
                                    text-[15px] text-[#232F20] cursor-pointer transition-all duration-300
                                    {{ $favoritesMode && (int) $activeTagId === (int) $tag->id
                                        ? ''
                                        : 'hover:bg-white hover:border-gray-100 hover:shadow-[0_4px_12px_rgba(0,0,0,0.12)]' }}"
                             data-favorite-category="{{ $tag->id }}">
                            {{ $tag->title }}
                            @if($favoritesMode)
                                <span class="ml-[4px] text-[14px] text-[#8c8c8c]">{{ $tag->favorite_count }}</span>
                                <button type="button"
                                        class="ml-[16px] inline-flex items-center text-[#DC092E] hover:text-[#a80723] transition-colors align-middle cursor-pointer"
                                        data-favorite-category-remove
                                        data-category-id="{{ $tag->id }}"
                                        data-category-title="{{ $tag->title }}"
                                        data-delete-url="{{ route('favorites.category.delete', ['category' => $tag->slug]) }}"
                                        aria-label="Удалить избранные товары категории {{ $tag->title }}">
                                    <svg class="w-[24px] h-[24px]" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                         aria-hidden="true">
                                        <path stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Скрипт переносим внутрь условия, чтобы он не грузился зря --}}
        <script>
        (function() { // Оборачиваем в анонимную функцию для изоляции переменных
            const initTags = () => {
                // Ищем ВСЕ контейнеры тегов на странице
                const containers = document.querySelectorAll('.js-tag-container');
                
                containers.forEach(container => {
                    const track = container.querySelector('.js-tag-track');
                    const prevWrapper = container.querySelector('.js-tag-prev-wrapper');
                    const nextWrapper = container.querySelector('.js-tag-next-wrapper');
                    const btnPrev = container.querySelector('.js-tag-prev');
                    const btnNext = container.querySelector('.js-tag-next');

                    if (!track || !btnPrev || !btnNext) return;

                    const scrollStep = 250;

                    const updateButtons = () => {
                        const scrollLeft = track.scrollLeft;
                        const maxScroll = track.scrollWidth - track.clientWidth;

                        if (scrollLeft > 5) {
                            prevWrapper.classList.remove('opacity-0', 'pointer-events-none');
                        } else {
                            prevWrapper.classList.add('opacity-0', 'pointer-events-none');
                        }

                        if (scrollLeft < maxScroll - 5) {
                            nextWrapper.classList.remove('opacity-0', 'pointer-events-none');
                        } else {
                            nextWrapper.classList.add('opacity-0', 'pointer-events-none');
                        }
                    };

                    btnNext.onclick = () => track.scrollBy({ left: scrollStep, behavior: 'smooth' });
                    btnPrev.onclick = () => track.scrollBy({ left: -scrollStep, behavior: 'smooth' });

                    track.onscroll = updateButtons;
                    window.addEventListener('resize', updateButtons);
                    setTimeout(updateButtons, 100);
                });
            };

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', initTags);
            } else {
                initTags();
            }
        })();
        </script>
    @endif
</div>