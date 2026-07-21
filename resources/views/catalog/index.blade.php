@extends('layouts.main')

@section('content')

<x-breadcrumbs :items="[
    ['title' => 'Главная', 'url' => route('home')],
    ['title' => 'Каталог']
]" />

<h1 class="text-[34px] font-bold mb-1">Каталог</h1>

<div class="mx-auto max-w-[1500px] grid grid-cols-5 gap-4">

    @foreach($groups as $group)
        <x-catalog-card
            :title="$group->title"
            :image="$group->image"
            :url="route('catalog.group', $group->slug)"
        />
    @endforeach

</div>

@endsection
