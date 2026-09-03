@extends('layouts.main')

@section('title', 'Главная')

@section('content')

    {{-- Секция товаров --}}
    <section class="w-full">
        <div class="max-w-[1500px] mx-auto">

            {{-- Слайдер баннеров --}}
            @include('components.one-image-banner-slider', ['one_image_banner_slider' => $main_banners])
            @include('components.subcategory-slider')

            @include('components.title-with-tags', [
                'slider_title' => 'Хиты продаж',
                'slider_tags' => $categoriesHit // твоя переменная из AppServiceProvider
            ])
            @include('catalog.partials.product-variants-slider', ['product_variants_slider' => $banner_bestsellers])
            @include('components.two-image-banner-slider', ['two_image_banner_slider' => $banner_bestsellers])



            @include('components.title-with-tags', [
                'slider_title' => 'Новинки',
                'slider_tags' => $categoriesHit // твоя переменная из AppServiceProvider
            ])
            @include('components.two-image-banner-slider', ['two_image_banner_slider' => $banner_bestsellers])
            @include('catalog.partials.product-variants-slider', ['product_variants_slider' => $banner_bestsellers])

        </div>
    </section>

@endsection
