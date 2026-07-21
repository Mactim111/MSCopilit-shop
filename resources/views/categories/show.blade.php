@extends('layouts.main')

@section('title', $category->title)

@section('content')

    <div class="max-w-[1500px] mx-auto px-6 py-10">

        {{-- Хлебные крошки --}}
        <nav class="text-sm text-gray-500 mb-4 flex gap-2">
            <a href="/" class="hover:text-gray-700">Главная</a>
            <span>/</span>
            <a href="{{ route('category.catalog') }}" class="hover:text-gray-700">Категории</a>
            <span>/</span>
            <span class="text-gray-700">{{ $category->title }}</span>
        </nav>

        {{-- Название + код товара в одной строке --}}
        <div class="flex justify-between items-center mb-2">
            <h1 class="text-3xl font-bold">{{ $category->title }}</h1>

            <div class="text-gray-500 text-sm">
                Код товара: {{ $category->id }}
            </div>
        </div>

    </div>

@endsection

