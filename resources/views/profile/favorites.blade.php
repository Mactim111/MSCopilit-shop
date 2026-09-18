@extends('layouts.main')

@section('title', 'Избранное')

@section('content')

@php
    $favoriteCount = auth()->check()
        ? auth()->user()->favorites()->count()
        : 0;
@endphp

{{-- Хлебные крошки --}}
<div class="max-w-[1500px] py-[24px] mx-auto flex items-center text-[13px] text-[#7b7979]">
    <x-breadcrumbs :items="[
        ['title' => 'Главная', 'url' => route('home')],
        ['title' => 'Личный кабинет', 'url' => route('profile')],
        ['title' => 'Избранное'],
    ]" />
</div>


{{-- Основная двухколоночная часть: 3 + 9 колонок --}}
<div class="max-w-[1500px] mx-auto flex mt-6 mb-[70px]">

        {{-- Левая колонка: фильтры --}}
        <aside class="w-full max-w-[348px] pr-[32px] flex flex-col">
            <div class="border border-gray-200 rounded-xl px-[24px] py-[16px] bg-white shadow-sm">

            </div>
        </aside>

        {{-- Правая колонка: резерв под блок характеристик + сортировка + список + пагинация --}}
        <main class="w-full max-w-[1152px] flex flex-col">
            {{-- Заголовок подкатегории + количество товаров --}}
            <div class="max-w-[1500px] flex h-[42px] mb-5 items-baseline">
                <h1 class="text-[26px] font-bold text-[#231F20] leading-none text-left">
                    Избранное
                </h1>
                <div class="ml-[10px] text-gray-400 text-xl w-[32px] h-[20px] leading-none">
                    {{ $favoriteCount }}
                </div>

            </div>
            
            @forelse($favorites as $variant)
                <x-variant-list-card :variant="$variant" :isFavorite="true" />
            @empty
                <p class="text-gray-600">В избранном пока нет товаров.</p>
            @endforelse
        </main>

</div>

@endsection



