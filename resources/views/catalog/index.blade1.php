@extends('layouts.main')

@section('title', 'Каталог товаров')

@section('content')

    <div class="py-10">

        <h1 class="text-3xl font-bold mb-8">Каталог товаров</h1>

        {{-- Фильтры --}}
        <form method="GET" class="bg-white p-6 rounded-xl shadow mb-10 grid grid-cols-1 md:grid-cols-4 gap-6">

            {{-- Категории --}}
            <div>
                <label class="block text-sm font-medium mb-1">Категория</label>
                <select name="category" class="w-full border border-gray-300 rounded-md">
                    <option value="">Все категории</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" @selected(request('category') == $cat->id)>
                            {{ $cat->title }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Цена от --}}
            <div>
                <label class="block text-sm font-medium mb-1">Цена от</label>
                <input type="number" name="price_from" value="{{ request('price_from') }}"
                       class="w-full border border-gray-300 rounded-md">
            </div>

            {{-- Цена до --}}
            <div>
                <label class="block text-sm font-medium mb-1">Цена до</label>
                <input type="number" name="price_to" value="{{ request('price_to') }}"
                       class="w-full border border-gray-300 rounded-md">
            </div>

            {{-- Сортировка --}}
            <div>
                <label class="block text-sm font-medium mb-1">Сортировка</label>
                <select name="sort" class="w-full border border-gray-300 rounded-md">
                    <option value="">По умолчанию</option>
                    <option value="price_asc" @selected(request('sort') == 'price_asc')>Цена ↑</option>
                    <option value="price_desc" @selected(request('sort') == 'price_desc')>Цена ↓</option>
                    <option value="new" @selected(request('sort') == 'new')>Новинки</option>
                </select>
            </div>

            <div class="md:col-span-4 flex justify-between">
                <button class="bg-red-600 text-white px-6 py-2 rounded-md hover:bg-red-700 transition">
                    Применить
                </button>
                <a href="{{ route('catalog') }}"
                   class="bg-gray-600 text-white px-6 py-2 rounded-md hover:bg-gray-700 transition">
                    Сбросить
                </a>

            </div>

        </form>

        {{-- Сетка товаров --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
            @foreach($products as $product)
                <x-product-card :product="$product" :isFavorite="$product->is_favorite ?? false" />
            @endforeach
        </div>

        <div class="mt-10">
            {{ $products->links() }}
        </div>

    </div>

@endsection

