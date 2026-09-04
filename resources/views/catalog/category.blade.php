@extends('layouts.main')

@section('content')

{{-- Хлебные крошки --}}
<div class="max-w-[1500px] py-[24px] mx-auto flex items-center text-[13px] text-[#7b7979]">
<x-breadcrumbs :items="[
    ['title' => 'Главная', 'url' => route('home')],
    ['title' => 'Каталог', 'url' => route('catalog.index')],
    ['title' => $group->title, 'url' => route('catalog.group', $group->slug)],
    ['title' => $category->title]
]" />
</div>

<h1 class="text-[34px] font-bold mb-5">{{ $category->title }}</h1>

<div class="mx-auto max-w-[1500px] grid grid-cols-5 gap-4">

    {{-- Реальные подкатегории --}}
    @foreach($subcategories as $sub)
        <x-catalog-card
            :title="$sub->title"
            :image="$sub->imageUrl()"
            :url="route('catalog.subcategory', [$group->slug, $category->slug, $sub->slug])"
        />
    @endforeach

    {{-- Брендовые плитки --}}
    @if($showBrandTiles)
        @include('catalog.partials.brand-tiles')
    @endif

</div>

@endsection
