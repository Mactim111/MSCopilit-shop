@extends('layouts.main')

@section('title', 'Главная')

@section('content')

    {{-- Слайдер баннеров --}}
    <div class="w-full mt-6">
        <div class="max-w-[1500px] mx-auto">
            @include('components.banner-slider', ['banners' => $banners])
        </div>
    </div>

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
