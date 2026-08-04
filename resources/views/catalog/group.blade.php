@extends('layouts.main')

@section('content')

<x-breadcrumbs :items="[
    ['title' => 'Главная', 'url' => route('home')],
    ['title' => 'Каталог', 'url' => route('catalog.index')],
    ['title' => $group->title]
]" />


<h1 class="text-2xl font-bold mb-6">{{ $group->title }}</h1>

<div class="mx-auto max-w-[1500px] grid grid-cols-5 gap-4">

    {{-- Реальные категории --}}
    @foreach($categories as $category)
        <x-catalog-card
            :title="$category->title"
            :image="$category->imageUrl()"
            :url="route('catalog.category', [$group->slug, $category->slug])"
        />
    @endforeach


</div>

@endsection
