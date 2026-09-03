{{-- компонент будет решать, какой именно слайдер рисовать. --}}

@props(['section', 'currentVariant' => null])

@inject('frontend', 'App\Services\FrontendService')

@php
    // Просим сервис подготовить данные для этой конкретной секции
    $data = $frontend->getSectionData($section, $currentVariant);
@endphp

{{-- Если данных нет — вообще ничего не рисуем --}}
@if($data['items']->count() > 0)
    
    {{-- Сначала рисуем заголовок и теги (наш универсальный компонент) --}}
    @include('components.title-with-tags', [
        'slider_title' => $data['title'],
        'slider_tags'  => $data['tags']
    ])

    {{-- Затем рисуем сам слайдер с карточками (6 шт. в ряд) вариантов товаров --}}
    {{-- Мы используем один и тот же компонент слайдера, просто передаем разные выборки вариантов товаров --}}
    @include('catalog.partials.product-variants-slider', [
        'popular_variants' => $data['items'] 
    ])

{{-- Если это двойной баннер --}}
@elseif($section->type == 'double_banner' && $data['items']->isNotEmpty())

    @include('components.two-image-banner-slider', [
        'two_image_banner_slider' => $data['items']
    ])

@endif  