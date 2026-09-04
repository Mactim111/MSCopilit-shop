@extends('layouts.main')

@section('content')

{{-- Хлебные крошки --}}
<div class="max-w-[1500px] py-[24px] mx-auto flex items-center text-[13px] text-[#7b7979]">
    <x-breadcrumbs :items="[
        ['title' => 'Главная', 'url' => route('home')],
        ['title' => 'Каталог', 'url' => route('catalog.index')],
        ['title' => $group->title]
    ]" />
</div>

<h1 class="text-[34px] font-bold mb-5">{{ $group->title }}</h1>

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
