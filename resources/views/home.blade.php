@extends('layouts.main')

@section('title', 'Главная')

@section('content')

    {{-- Слайдер баннеров --}}
    <div class="w-full mt-6">
        <div class="max-w-[1500px] mx-auto">
            @include('components.one-image-banner-slider', ['one_image_banner_slider' => $main_banners])
        </div>
    </div>

    <div class="max-w-[1500px] mx-auto">
        @include('components.subcategory-slider')
    </div>

    {{-- Секция товаров --}}
    <section class="w-full py-5">
        <div class="max-w-[1500px] mx-auto">

            {{-- <h2 class="text-2xl font-bold pb-2 mb-4">
                Популярные товары
            </h2> --}}

            {{-- <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-6">
                @foreach($popular_variants as $variant)
                    <x-variant-card :variant="$variant" />
                @endforeach
            </div> --}}

            @include('catalog.partials.popular_products_slider')
            @include('components.two-image-banner-slider', ['two_image_banner_slider' => $banner_bestsellers])

        </div>
    </section>

@endsection
