@extends('layouts.main')

@section('title', $product->title)

@section('content')

    <div class="mx-auto py-10 mb-70">

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
            $product->lineup
                ? ['title' => $product->lineup, 'url' => '#']
                : null,

            // Вариант товара
            ['title' => $variant->title]
        ])" />


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
            {{-- <div class="lg:col-span-4"> --}}

                {{-- Главное изображение --}}
                
                    {{-- <div class="flex items-center justify-center overflow-hidden 
                        border border-gray-100 rounded-lg bg-gray-100 
                        shadow-[0_2px_8px_rgba(0,0,0,0.20)]
                        cursor-pointer transition-all duration-200 w-[510px] h-[510px]"
                    >
                        <img id="main-image"
                            src="{{ $variant->mainImage() }}"
                            class="object-contain">
                    </div> --}}
                


                {{-- Галерея миниатюр с прокруткой --}}
                {{-- <div class="relative"> --}}

                    {{-- Кнопка назад --}}
                    {{-- <button 
                        id="thumb-left"
                        class="absolute left-[1px] top-1/2 -translate-y-1/2
                            w-[32px] h-[32px] rounded-full bg-white border border-gray-200 shadow-md
                            flex items-center justify-center cursor-pointer z-10">
                        <span class="text-red-600">
                            @include('products.icons.chevron-left-thin')
                        </span>
                    </button> --}}

                    {{-- Лента миниатюр --}}
                    {{-- <div id="thumb-strip"
                        class="flex overflow-x-auto scrollbar-hide px-10 py-2">

                        @foreach($variant->images as $img)
                            <div class="w-[80px] h-[76px] flex items-center justify-center
                                    bg-ray-200 
                                    p-[8px] px-[12px]">

                                <div class="w-[58px] h-[58px] flex items-center justify-center 
                                    border border-gray-100 rounded bg-white 
                                    shadow
                                    ">
                                    <img src="{{ $img->url }}"
                                        class="object-cover rounded w-[52px] h-[52px]"
                                        onclick="document.getElementById('main-image').src=this.src">
                                </div>
                            </div>

                        @endforeach
                    </div> --}}

                    {{-- Кнопка вперед --}}
                    {{-- <button 
                        id="thumb-right"
                        class="absolute right-[1px] top-1/2 -translate-y-1/2
                            w-[32px] h-[32px] rounded-full bg-white border border-gray-200 shadow-md
                            flex items-center justify-center cursor-pointer z-10">
                        <span class="text-red-600">
                            @include('products.icons.chevron-right-thin')
                        </span>
                    </button>

                </div>

            </div> --}}
            
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
                        <!-- Кнопка лупы -->
                        {{-- <button class="absolute bottom-[12px] right-[12px] w-[18px] h-[18px]
                                    bg-white rounded-full shadow-md flex items-center justify-center
                                    cursor-pointer js-open-modal">
                            @include('products.icons.zoom-plus')
                        </button> --}}
                    </div>
                


                {{-- Галерея миниатюр с прокруткой --}}
                <div class="relative">

                    {{-- Кнопка назад --}}
                    @if($variant->images->count() > 5)
                        <button 
                            id="thumb-left"
                            class="absolute left-[1px] top-1/2 -translate-y-1/2
                                w-[32px] h-[32px] rounded-full bg-white border border-gray-200 shadow-md
                                flex items-center justify-center cursor-pointer z-10">
                            <span class="text-red-600">
                                @include('products.icons.chevron-left-thin')
                            </span>
                        </button>
                    @endif
                    
                    {{-- Лента миниатюр --}}
                    <div id="thumb-strip" class="flex overflow-x-auto scrollbar-hide px-10 py-2">

                        @foreach($variant->images as $img)
                            {{-- <div class="w-[80px] h-[76px] flex items-center justify-center
                                    bg-ray-200 
                                    p-[8px] px-[12px]"> --}}

                                {{-- <div class="w-[58px] h-[58px] flex items-center justify-center 
                                    border border-gray-100 rounded bg-white 
                                    shadow
                                    ">
                                    <img src="{{ $img->url }}"
                                        class="object-cover rounded w-[52px] h-[52px]
                                        {{ $img->id === $activeId ? 'border-black' : 'border-gray-300' }} js-thumb"
                                        data-id="{{ $img->id }}"
                                    >
                                </div> --}}
                                <div class="w-[80px] h-[76px] flex items-center justify-center p-[8px] px-[12px]">

                                    <div class="thumb-wrapper w-[58px] h-[58px] flex items-center justify-center 
                                        rounded bg-white shadow border
                                        {{ $img->id === $activeId ? 'border-black' : 'border-gray-300' }}">
                                        
                                        <img src="{{ $img->url }}"
                                            class="object-cover rounded w-[52px] h-[52px] js-thumb"
                                                {{ $img->id === $activeId ? '' : 'grayscale' }}
                                            data-id="{{ $img->id }}">
                                    </div>
                                    
                                </div>

                            {{-- </div> --}}

                        @endforeach
                    </div>

                    {{-- Кнопка вперед --}}
                    @if($variant->images->count() > 5)
                        <button 
                            id="thumb-right"
                            class="absolute right-[1px] top-1/2 -translate-y-1/2
                                w-[32px] h-[32px] rounded-full bg-white border border-gray-200 shadow-md
                                flex items-center justify-center cursor-pointer z-10">
                            <span class="text-red-600">
                                @include('products.icons.chevron-right-thin')
                            </span>
                        </button>
                    @endif
                    
                </div>

            </div>

            {{-- Колонка 2: Основные характеристики (excerpt) — 4/12 --}}
            @include('variants.partials.switcher')
            <div class="lg:col-span-4 order-last lg:order-none">

                <div class="flex flex-col pl-[70px]">
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

    {{-- Блок "Рекомендуемые товары" --}}
    @include('catalog.partials.similar')

    {{-- Блок "Ранее вы смотрели" --}}
    <!-- @include('catalog.partials.recently-viewed') -->
    @include('catalog.partials.recently-viewed2')

    <x-variant-image-modal-window 
    :images="$variant->images"
    :activeId="$activeId"
    :activeImage="$activeImage"
    />


    

    {{-- JS для прокрутки миниатюр --}}
    {{-- <script>
        const strip = document.getElementById('thumb-strip');
        document.getElementById('thumb-left').onclick = () => strip.scrollBy({ left: -200, behavior: 'smooth' });
        document.getElementById('thumb-right').onclick = () => strip.scrollBy({ left: 200, behavior: 'smooth' });
    </script> --}}

    <script>
        document.addEventListener('DOMContentLoaded', () => {

            let activeId = {{ $activeId }};
            const images = @json($images);

            const mainImage = document.getElementById('main-image');
            const modalMainImage = document.getElementById('modal-main-image');

            const thumbs = document.querySelectorAll('.js-thumb');
            const modalThumbs = document.querySelectorAll('.js-modal-thumb');

            const modal = document.getElementById('gallery-modal');
            const openModalBtn = document.querySelector('.js-open-modal');
            const closeModalBtn = document.getElementById('close-modal');

            const prevBtn = document.querySelector('.js-modal-prev');
            const nextBtn = document.querySelector('.js-modal-next');

            function updateActive(id) {
                activeId = id;

                const img = images.find(i => i.id === id);

                mainImage.style.opacity = 0;
                modalMainImage.style.opacity = 0;

                setTimeout(() => {
                    mainImage.src = img.url;
                    modalMainImage.src = img.url;

                    mainImage.style.opacity = 1;
                    modalMainImage.style.opacity = 1;
                }, 150);

                // thumbs.forEach(t => {
                //     t.classList.remove('border-black');
                //     t.classList.add('border-gray-300');
                //     if (parseInt(t.dataset.id) === id) {
                //         t.classList.remove('border-gray-300');
                //         t.classList.add('border-black');
                //     }
                // });

                thumbs.forEach(t => {
                    const wrapper = t.closest('.thumb-wrapper');

                    // рамка
                    wrapper.classList.remove('border-black');
                    wrapper.classList.add('border-gray-300');

                    // grayscale
                    t.classList.add('grayscale');

                    if (parseInt(t.dataset.id) === id) {
                        wrapper.classList.remove('border-gray-300');
                        wrapper.classList.add('border-black');

                        t.classList.remove('grayscale');
                    }
                });

                modalThumbs.forEach(t => {
                    t.classList.add('grayscale');
                    if (parseInt(t.dataset.id) === id) {
                        t.classList.remove('grayscale');
                    }
                });
            }

            openModalBtn.addEventListener('click', () => {
                modal.classList.remove('hidden');
                modal.style.opacity = 0;

                setTimeout(() => {
                    modal.style.opacity = 1;
                }, 50);
            });

            closeModalBtn.addEventListener('click', () => {
                modal.style.opacity = 0;
                setTimeout(() => {
                    modal.classList.add('hidden');
                }, 150);
            });

            thumbs.forEach(t => {
                t.addEventListener('click', () => {
                    updateActive(parseInt(t.dataset.id));
                });
            });

            modalThumbs.forEach(t => {
                t.addEventListener('click', () => {
                    updateActive(parseInt(t.dataset.id));
                });
            });

            function nextImage() {
                const idx = images.findIndex(i => i.id === activeId);
                const next = images[(idx + 1) % images.length];
                updateActive(next.id);
            }

            function prevImage() {
                const idx = images.findIndex(i => i.id === activeId);
                const prev = images[(idx - 1 + images.length) % images.length];
                updateActive(prev.id);
            }

            nextBtn.addEventListener('click', nextImage);
            prevBtn.addEventListener('click', prevImage);

        });
    </script>


@endsection
