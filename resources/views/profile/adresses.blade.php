@extends('layouts.main')

@section('title', 'Адрес доставки')

@section('content')

{{-- Хлебные крошки --}}
<div class="max-w-[1500px] py-[24px] mx-auto flex items-center text-[13px] text-[#7b7979]">
    <x-breadcrumbs :items="[
        ['title' => 'Главная', 'url' => route('home')],
        ['title' => 'Личный кабинет', 'url' => route('profile')],
        ['title' => 'Адрес доставки'],
    ]" />
</div>

{{-- Основная двухколоночная часть --}}
<div class="max-w-[1500px] mx-auto flex flex-col gap-6 mt-6 mb-[70px] md:flex-row">

        <x-profile-sidebar active="addresses" />

        {{-- Правая колонка: заголовок с тегами + список вариантов + пагинация --}}
        <main class="w-full max-w-[1152px] flex flex-col">
            <div class="flex" id="addresses-heading">
                <h1 class="h-[42px] flex items-center mb-6 mr-5 text-[26px] font-semibold leading-none text-[#231f20]">
                    Адрес доставки
                </h1>
            </div>

            {{-- Список карточек избранных вариантов вынесен в partial для AJAX-замены. --}}
            @include('profile.partials.addresses-list')
        </main>

</div>