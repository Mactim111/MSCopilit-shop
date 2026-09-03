{{-- компонент будет решать, какой именно слайдер рисовать. --}}

@props(['section', 'currentVariant' => null])

@inject('frontend', 'App\Services\FrontendService')

@php
    // Просим сервис подготовить данные для этой конкретной секции
    $data = $frontend->getSectionData($section, $currentVariant);
@endphp

{{-- Выбор шаблона в зависимости от типа из БД --}}
@switch($section->type)

    @case('one_banner')
        @include('components.one-image-banner-slider', ['one_image_banner_slider' => $data['items']])
        @break

    @case('subcategory_cards')
        @include('components.subcategory-slider') {{-- Он сам берет данные из shared --}}
        @break

    @case('product_slider')
        @include('components.title-with-tags', [
            'slider_title' => $data['title'],
            'slider_tags'  => $data['tags']
        ])
        @include('catalog.partials.product-variants-slider', [
            'product_variants_slider' => $data['items'] 
        ])
        @break

    @case('double_banner')
        @include('components.two-image-banner-slider', [
            'two_image_banner_slider' => $data['items']
        ])
        @break

    @case('recently_viewed')
        {{-- Сначала заголовок --}}
        @include('components.title-with-tags', ['slider_title' => $data['title']])
        
        {{-- Затем исправленный слайдер --}}
        @include('catalog.partials.recently-viewed-slider', [
            'recently_viewed_slider' => $data['items']
        ])
        @break

@endswitch