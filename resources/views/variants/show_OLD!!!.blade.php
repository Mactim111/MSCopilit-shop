@extends('layouts.main')

@section('title', $product->title)

@section('content')

    <div class="mx-auto mb-[70px]">

        <div class="py-6 text-[13px]">
            {{-- Хлебные крошки --}}
            <x-breadcrumbs :items="array_filter([
                ['title' => 'Главная', 'url' => route('home')],
                ['title' => 'Каталог', 'url' => route('catalog.index')],

                // Подкатегория
                [
                    'title' => $subcategory->title,
                    'url' => route('catalog.subcategory', [
                        $group->slug,
                        $category->slug,
                        $subcategory->slug
                    ])
                ],

                // Бренд
                [
                    'title' => $brand->title,
                    'url' => route('catalog.subcategory.brand', [
                        $group->slug,
                        $category->slug,
                        $subcategory->slug,
                        $brand->slug
                    ]) 
                ],

                // Линейка (если есть)
                // $product->lineup
                //     ? ['title' => $product->lineup, 'url' => '#']
                //     : null,

                // Вариант товара
                ['title' => $variant->title]
            ])" />
        </div>


        {{-- Название + код товара в одной строке --}}
        <div class="flex justify-between items-center mb-2">
            <h1 class="text-[#231f20] text-[28px] font-bold">{{ $variant->title }}</h1>

            <div class="text-[#231f20] text-[14px]">
                Код товара: {{ $variant->article }}
            </div>
        </div>

        {{-- Вкладки --}}
        <div class="border-b border-gray-200 mb-8">
            <ul class="flex gap-8 text-lg font-medium">
                <li class="pb-3 border-b-2 border-red-600 text-red-600 cursor-pointer">
                    Основное
                </li>
                <li class="pb-3 text-gray-500 cursor-pointer hover:text-gray-700">
                    Характеристики
                </li>
                <li class="pb-3 text-gray-500 cursor-pointer hover:text-gray-700">
                    Отзывы
                </li>
            </ul>
        </div>

        {{-- Трёхколоночная структура --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            
            {{-- Колонка 1: Галерея (занимает 5/12) --}}
            <div class="lg:col-span-4">

                {{-- Большая картинка --}}
                
                <div class="flex items-center justify-center overflow-hidden 
                    {{-- border border-gray-100 rounded-lg bg-gray-100 
                    shadow-[0_2px_8px_rgba(0,0,0,0.20)] --}}
                    zoom-cursor transition-all duration-200 w-[510px] h-[510px]
                    relative  js-open-modal"
                >
                    <img id="main-image"
                        src="{{ $activeImage }}"
                        class="object-contain"
                    >
                </div>

                {{-- Галерея миниатюр с прокруткой --}}
                @if($variant->images->count() >= 5)
                <div class="max-w-[510px] overflow-hidden mx-auto relative">

                    <div class="relative px-[16px]">

                        {{-- Кнопка назад --}}
                        <button type="button"
                            class="js-swiper-prev absolute left-[1px] top-1/2 -translate-y-1/2
                                w-[32px] h-[32px] rounded-full bg-white border border-gray-200 shadow-md
                                flex items-center justify-center cursor-pointer z-10">
                            <span class="text-red-600">@include('products.icons.chevron-left-thin')</span>
                        </button>

                        {{-- SWIPER --}}
                        <div class="swiper js-swiper"
                            data-grab="true"
                            data-loop="true"
                            data-pagination="false"
                            data-navigation="true"
                            data-space="-20"
                            data-slides="5">

                            <div class="swiper-wrapper">

                                @foreach($variant->images as $img)
                                <div class="swiper-slide min-h-[76px] py-[6px]">

                                    <div class="w-[80px] h-[76px] flex items-center justify-center mx-auto">
                                        <div class="thumb-wrapper w-[58px] h-[58px] flex items-center justify-center 
                                            rounded bg-white shadow border
                                            {{ $img->id === $activeId ? 'border-black' : 'border-gray-300' }}">
                                            
                                            <img src="{{ $img->url }}"
                                                class="object-cover rounded w-[52px] h-[52px] js-thumb
                                                        {{ $img->id === $activeId ? '' : 'grayscale' }}"
                                                data-id="{{ $img->id }}">
                                        </div>
                                    </div>

                                </div>
                                @endforeach

                            </div>
                        </div>

                        {{-- Кнопка вперед --}}
                        <button type="button"
                            class="js-swiper-next absolute right-[1px] top-1/2 -translate-y-1/2
                                w-[32px] h-[32px] rounded-full bg-white border border-gray-200 shadow-md
                                flex items-center justify-center cursor-pointer z-10">
                            <span class="text-red-600">@include('products.icons.chevron-right-thin')</span>
                        </button>

                    </div>
                </div>
                @endif

            </div>

            {{-- Колонка 2: Основные характеристики (excerpt) — 4/12 --}}
            
            <div class="lg:col-span-4 order-last lg:order-none">
                

                <div class="flex flex-col pl-[70px]">
                    @include('variants.partials.switcher')
                    <h2 class="text-[15px] font-bold mb-4">Основные характеристики</h2>

                    <div class="text-gray-700 leading-relaxed mb-4">
                        <!-- {!! nl2br(str_replace(['\n', '\"'], ["\n", '"'], $variant->excerpt)) !!} -->
                         {!! $variant->formatted_excerpt !!}
                    </div>

                    <a href="#full-specs" class="text-blue-600 hover:underline">
                        Все характеристики
                    </a>
                </div>    
        
            </div>

            {{-- Колонка 3: Карточка корзины — 4/12 --}}
            <div class="lg:col-span-4">
                <div class="flex flex-col" style="padding-left:30px;">
                <x-variant-buy-card :variant="$variant" :isFavorite="$isFavorite ?? false" />
                </div>
            </div>

        </div>

        {{-- Полный блок характеристик --}}
        <div id="full-specs" class="mt-16">
            <!-- <h2 class="text-2xl font-bold mb-4">Основные характеристики</h2> -->

            <div class="text-gray-700 leading-relaxed">
                <x-specs-table :text="$variant->description" />
                <!-- {!! nl2br(str_replace(['\n', '\"'], ["\n", '"'], $variant->description)) !!} -->
            </div>
        </div>

    </div>

    {{-- Блок "Похожие товары" --}}
    {{-- Ниже закомментировано подключение старого компонента - проверить, нужен ли ОН еще, если не нужен - удалить, так как ниже перешли на универсальные блоки заголовка, 
    тегов, слайдера --}}
    {{-- @include('catalog.partials.similar') --}}
    @include('components.title-with-tags', [
                'slider_title' => 'Похожие товары',
            ])
    @include('catalog.partials.product-variants-slider', ['product_variants_slider' => $popular_variants])

    {{-- Блок "Покупают вместе" --}}
    @include('components.title-with-tags', [
                'slider_title' => 'Покупают вместе',
                'slider_tags' => $categoriesHit // наша переменная из AppServiceProvider
            ])
    @include('catalog.partials.product-variants-slider', ['product_variants_slider' => $popular_variants])

    {{-- Блок "Ранее вы смотрели" --}}
    @include('components.title-with-tags', [
                'slider_title' => 'Ранее вы смотрели',
            ])
    @include('catalog.partials.recently-viewed-slider', ['recently_viewed_slider' => $popular_variants])

    <x-variant-image-modal-window 
    :images="$variant->images"
    :activeId="$activeId"
    :activeImage="$activeImage"
    />

{{-- JS для прокрутки миниатюр --}}
<script>
document.addEventListener('DOMContentLoaded', () => {

    let activeId = {{ $activeId }};
    const images = @json($images);

    const mainImage = document.getElementById('main-image');
    const modalMainImage = document.getElementById('modal-main-image');

    const modal = document.getElementById('gallery-modal');
    const openModalBtn = document.querySelector('.js-open-modal');
    const closeModalBtn = document.getElementById('close-modal');

    // Закрытие модалки по клику на боковые колонки
    document.querySelectorAll('.js-modal-overlay').forEach(overlay => {
        overlay.addEventListener('click', () => {
            modal.classList.add('hidden');
        });
    });

    // Закрытие модалки по клавише Esc
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            modal.classList.add('hidden');
        }
    });

    const modalPrevBtn = document.querySelector('.js-modal-prev');
    const modalNextBtn = document.querySelector('.js-modal-next');

    const swiperEl = document.querySelector('.js-swiper');
    if (!swiperEl) return;

    // Функция обновления визуального состояния (рамка, grayscale, главное фото)
    function updateActive(id) {
        activeId = id;
        const img = images.find(i => i.id === id);
        if (!img) return;

        // Обновляем главное фото и модалку
        if (mainImage) mainImage.src = img.url;
        if (modalMainImage) modalMainImage.src = img.url;

        // Обновляем ВСЕ миниатюры в слайдере (включая клоны Swiper!)
        document.querySelectorAll('.js-thumb').forEach(t => {
            const wrapper = t.closest('.thumb-wrapper');
            const thumbId = parseInt(t.dataset.id);

            if (thumbId === id) {
                t.classList.remove('grayscale');
                if (wrapper) {
                    wrapper.classList.remove('border-gray-300');
                    wrapper.classList.add('border-black');
                }
            } else {
                t.classList.add('grayscale');
                if (wrapper) {
                    wrapper.classList.remove('border-black');
                    wrapper.classList.add('border-gray-300');
                }
            }
        });

        // Обновляем миниатюры в модальном окне
        document.querySelectorAll('.js-modal-thumb').forEach(t => {
            if (parseInt(t.dataset.id) === id) {
                t.classList.remove('grayscale');
            } else {
                t.classList.add('grayscale');
            }
        });
    }

    // Ждем инициализации Swiper из app.js
    const initSwiperSync = () => {
        const swiperInstance = swiperEl.swiper;
        if (!swiperInstance) return;

        // При пролистывании слайдера (стрелками или свайпом)
        swiperInstance.on('slideChange', () => {
            // realIndex дает точный индекс оригинального массива даже при loop: true
            const realIdx = swiperInstance.realIndex;
            if (images[realIdx]) {
                updateActive(images[realIdx].id);
            }
        });

        // Клик по любой миниатюре в слайдере
        document.addEventListener('click', (e) => {
            const thumb = e.target.closest('.js-thumb');
            if (thumb) {
                const id = parseInt(thumb.dataset.id);
                const index = images.findIndex(i => i.id === id);
                if (index !== -1) {
                    swiperInstance.slideToLoop(index);
                    updateActive(id);
                }
            }
        });

        // Клик по миниатюрам в модальном окне
        document.querySelectorAll('.js-modal-thumb').forEach(t => {
            t.addEventListener('click', () => {
                const id = parseInt(t.dataset.id);
                const index = images.findIndex(i => i.id === id);
                if (index !== -1) {
                    swiperInstance.slideToLoop(index);
                    updateActive(id);
                }
            });
        });

        // Кнопки Назад / Вперед в Модальном окне
        if (modalNextBtn) {
            modalNextBtn.addEventListener('click', () => {
                swiperInstance.slideNext();
            });
        }
        if (modalPrevBtn) {
            modalPrevBtn.addEventListener('click', () => {
                swiperInstance.slidePrev();
            });
        }
    };

    // Запускаем синхронизацию (с небольшой задержкой на случай таймингов Vite/app.js)
    setTimeout(initSwiperSync, 50);

    // Логика открытия/закрытия модалки
    if (openModalBtn) {
        openModalBtn.addEventListener('click', () => modal.classList.remove('hidden'));
    }
    if (closeModalBtn) {
        closeModalBtn.addEventListener('click', () => modal.classList.add('hidden'));
    }
});
</script>

@endsection
