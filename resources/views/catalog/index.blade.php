@extends('layouts.main')

@section('content')

{{-- Хлебные крошки --}}
<div class="max-w-[1500px] py-[24px] mx-auto flex items-center text-[13px] text-[#7b7979]">
<x-breadcrumbs :items="[
    ['title' => 'Главная', 'url' => route('home')],
    ['title' => 'Каталог']
]" />
</div>

<h1 class="text-[34px] font-bold mb-5">Каталог</h1>

<div class="mx-auto max-w-[1500px] grid grid-cols-5 gap-4">

    {{-- Фейковая плитка "Акции" --}}
    <x-catalog-card
        title="Акции"
        image="/storage/assets/img/actions.png"
        url=""
    />

    @foreach($groups as $group)
        <x-catalog-card
            :title="$group->title"
            :image="$group->image"
            :url="route('catalog.group', $group->slug)"
        />
    @endforeach

</div>

@endsection
