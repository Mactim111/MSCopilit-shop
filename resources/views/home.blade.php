@extends('layouts.main')

@section('title', 'Главная')

@section('content')

    {{-- Слайдер баннеров --}}
    <div class="w-full mt-6">
        <div class="max-w-[1500px] mx-auto">
            @include('components.banner-slider')
        </div>
    </div>

    {{-- Hero секция --}}
    {{--    <section class="bg-gradient-to-r from-red-600 to-red-800 text-white py-20 w-full">--}}
    {{--        <div class="max-w-[1500px] mx-auto px-6 text-center">--}}
    {{--            <h1 class="text-4xl md:text-5xl font-bold mb-4">--}}
    {{--                Добро пожаловать в MyShop--}}
    {{--            </h1>--}}
    {{--            <p class="text-lg md:text-xl opacity-90">--}}
    {{--                Лучшие товары по отличным ценам--}}
    {{--            </p>--}}

    {{--            --}}{{-- Фильтр по названию --}}
    {{--            <div class="max-w-3xl mx-auto mt-10">--}}
    {{--                <form method="GET" action="{{ route('home') }}" class="flex items-center gap-3">--}}

    {{--                    <input type="text"--}}
    {{--                           name="search"--}}
    {{--                           value="{{ request('search') }}"--}}
    {{--                           placeholder="Поиск товаров..."--}}
    {{--                           class="w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-red-500 focus:border-red-500">--}}

    {{--                    <button class="px-6 py-3 bg-red-600 text-white rounded-lg hover:bg-red-700 transition">--}}
    {{--                        Найти--}}
    {{--                    </button>--}}

    {{--                    @if(request('search'))--}}
    {{--                        <a href="{{ route('home') }}" class="px-4 py-3 text-gray-600 hover:text-black">--}}
    {{--                            Сбросить--}}
    {{--                        </a>--}}
    {{--                    @endif--}}
    {{--                </form>--}}
    {{--            </div>--}}
    {{--        </div>--}}
    {{--    </section>--}}

    {{-- Секция товаров --}}
    <section class="w-full py-12 bg">
        <div class="max-w-[1500px] mx-auto">

            <h2 class="text-2xl font-bold pb-2 mb-4">
                Популярные товары
            </h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-6">
                @foreach($popular_variants as $variant)
                    <x-variant-card :variant="$variant" />
                @endforeach
            </div>

        </div>
    </section>

@endsection
