@extends('layouts.main')

@section('title', 'Мои отзывы')

@section('content')

{{-- Хлебные крошки --}}
<div class="max-w-[1500px] py-[24px] mx-auto flex items-center text-[13px] text-[#7b7979]">
    <x-breadcrumbs :items="[
        ['title' => 'Главная', 'url' => route('home')],
        ['title' => 'Личный кабинет', 'url' => route('profile')],
        ['title' => 'Мои отзывы'],
    ]" />
</div>


{{-- Основная двухколоночная часть --}}
<div class="max-w-[1500px] mx-auto flex flex-col gap-6 mt-6 mb-[70px] md:flex-row">

        <x-profile-sidebar active="reviews" />

        {{-- Правая колонка: заголовок с тегами + список вариантов + пагинация --}}
        <main class="w-full max-w-[1152px] flex flex-col">
            <div id="reviews-heading">

                    <div class="h-[56px] flex items-end">
                        <h1 class="pb-[16px] text-[26px] font-semibold text-[#231f20] leading-none">
                            Мои отзывы
                        </h1>
                    </div>

            </div>

            {{-- Список карточек избранных вариантов вынесен в partial для AJAX-замены. --}}
            @include('profile.partials.reviews-list')

            
        </main>

</div>

@endsection
