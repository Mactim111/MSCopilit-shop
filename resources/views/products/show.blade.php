@extends('layouts.main')

@section('title', $product->title)

@section('content')

    <div class="mx-auto px-6 py-10">

        {{-- Хлебные крошки --}}
        <x-breadcrumbs :items="[
            ['title' => 'Главная', 'url' => route('home')],
            ['title' => 'Каталог', 'url' => route('catalog.index')],

            // Подкатегория
            [
                'title' => $product->category->title,
                'url' => route('catalog.subcategory', $product->category->slug)
            ],

            // Товар
            ['title' => $product->title]
        ]" />


        {{-- Название + код товара в одной строке --}}
        <div class="flex justify-between items-center mb-2">
            <h1 class="text-3xl font-bold">{{ $product->title }}</h1>

            <div class="text-gray-500 text-sm">
                Код товара: {{ $product->id }}
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
            <div class="lg:col-span-5">

                {{-- Главное изображение --}}
                <img id="main-image"
                     src="{{ $product->image_url }}"
                     class="w-full h-[420px] object-cover rounded-xl shadow mb-4">

                {{-- Галерея миниатюр с прокруткой --}}
                <div class="relative">

                    {{-- Левая стрелка --}}
                    <button id="thumb-left"
                            class="absolute left-0 top-1/2 -translate-y-1/2 bg-white shadow p-2 rounded-full z-10">
                        ‹
                    </button>

                    {{-- Лента миниатюр --}}
                    <div id="thumb-strip"
                         class="flex gap-3 overflow-x-auto scrollbar-hide px-10 py-2">
                        @foreach($product->images as $img)
                            <img src="{{ $img->url }}"
                                 class="w-24 h-24 object-cover rounded cursor-pointer border border-gray-200 hover:opacity-75"
                                 onclick="document.getElementById('main-image').src=this.src">
                        @endforeach
                    </div>

                    {{-- Правая стрелка --}}
                    <button id="thumb-right"
                            class="absolute right-0 top-1/2 -translate-y-1/2 bg-white shadow p-2 rounded-full z-10">
                        ›
                    </button>

                </div>

            </div>

            {{-- Колонка 2: Основные характеристики (excerpt) — 4/12 --}}
            <div class="lg:col-span-4 order-last lg:order-none">

                <h2 class="text-2xl font-bold mb-4">Основные характеристики</h2>

                <div class="text-gray-700 leading-relaxed mb-4">
                    {!! nl2br(e($product->excerpt)) !!}
                </div>

                <a href="#full-specs" class="text-blue-600 hover:underline">
                    Все характеристики
                </a>

            </div>

            {{-- Колонка 3: Карточка корзины — 3/12 --}}
            <div class="lg:col-span-3">
                <x-product-buy-card :product="$product" :isFavorite="$isFavorite ?? false" />
            </div>

        </div>

        {{-- Похожие товары --}}
        <div class="mt-16">
            <h2 class="text-2xl font-bold mb-6">Похожие товары</h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
                @foreach($relatedProducts as $related)
                    <x-product-card :product="$related" />
                @endforeach
            </div>
        </div>

        {{-- Полный блок характеристик --}}
        <div id="full-specs" class="mt-16">
            <h2 class="text-2xl font-bold mb-4">Основные характеристики</h2>

            <div class="text-gray-700 leading-relaxed">
                {!! nl2br(e($product->description)) !!}
            </div>
        </div>

    </div>

    {{-- JS для прокрутки миниатюр --}}
    <script>
        const strip = document.getElementById('thumb-strip');
        document.getElementById('thumb-left').onclick = () => strip.scrollBy({ left: -200, behavior: 'smooth' });
        document.getElementById('thumb-right').onclick = () => strip.scrollBy({ left: 200, behavior: 'smooth' });
    </script>

@endsection
