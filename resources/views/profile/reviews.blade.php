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
<div class="max-w-[1500px] mx-auto flex flex-col gap-8 mt-6 mb-[70px] md:flex-row">

        <x-profile-sidebar active="reviews" />

        {{-- Правая колонка: фильтры и список отзывов пользователя --}}
        <main class="w-full max-w-[1152px] flex flex-col">
            <div class="flex"
                 id="reviews-heading">
                <h1 class="h-[42px] flex items-center mb-6 mr-5 text-[26px] font-semibold leading-none text-[#231f20]">
                    Мои отзывы
                </h1>

                <div class="mb-5">
                    <nav class="flex flex-wrap p-2.5 mt-[-10px] mx-[-14px] items-center"
                        aria-label="Фильтр отзывов" data-review-filter-nav>
                        @foreach([
                            'all' => 'Все',
                            'approved' => 'Одобренные',
                            'rejected' => 'Отклонённые',
                        ] as $filterKey => $filterLabel)
                            @php($isActiveFilter = $filter === $filterKey)
                            <div class="p-1.5">
                                <a href="{{ route('profile.reviews', ['filter' => $filterKey]) }}"
                                data-review-filter="{{ $filterKey }}"
                                @if($isActiveFilter) aria-current="page" @endif
                                class="inline-flex h-[36px] items-center justify-center border px-[12px] py-[5px] text-center text-[15px] text-[#231f20] transition-all
                                {{ $isActiveFilter
                                        ? 'rounded-[4px] border-black bg-white font-semibold'
                                        : 'rounded-[4px] border-[#f4f4f4] bg-[#f4f4f4] hover:border-gray-50 hover:shadow-[0_4px_12px_rgba(0,0,0,0.12)]' }}">
                                    {{ $filterLabel }}
                                    <span class="ml-[4px] text-[12px] font-normal text-[#8c8c8c]"
                                        data-review-filter-count="{{ $filterKey }}">{{ $reviewCounts[$filterKey] }}</span>
                                </a>
                            </div>
                        @endforeach
                    </nav>
                </div>
            </div>

            <div data-review-filter-results aria-live="polite">
                @include('profile.partials.reviews-list')
            </div>

        </main>

</div>

@auth
    @include('variants.partials.review-addition-modal')
    @include('variants.partials.review-action-result-modal')
@endauth

@endsection
