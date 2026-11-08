@extends('admin.layouts.admin')

@section('header-title', 'Отзывы')

@section('content')
<div class="mx-auto flex w-full max-w-[1300px] flex-col">

    <div class="flex flex-col items-start">
        <h1 class="mb-4 text-[26px] font-semibold leading-none text-[#231f20]">Отзывы</h1>

        <nav class="mb-4 flex flex-wrap" aria-label="Фильтр отзывов" data-review-filter-nav>
            @foreach([
                'all' => 'Все',
                'pending' => 'На модерации',
                'approved' => 'Одобренные',
                'rejected' => 'Отклонённые',
                'with_addition' => 'С дополнением к отзыву',
                'with_reply' => 'С ответом к отзыву',
                'deleted' => 'Удалённые',
                'deleted_by_customer' => 'Удалены автором',
                'deleted_by_admin' => 'Удалены модератором',
            ] as $filterKey => $filterLabel)
                @php($isActiveFilter = $filter === $filterKey)
                <div class="p-1.5 {{ $reviewCounts[$filterKey] === 0 ? 'hidden' : '' }}">
                    <a href="{{ route('admin.reviews.index', ['filter' => $filterKey, 'sort' => $sort]) }}"
                    data-review-filter="{{ $filterKey }}"
                    @if($isActiveFilter) aria-current="page" @endif
                    class="inline-flex h-[36px] items-center justify-center border px-[12px] py-[5px] text-center text-[15px] text-[#231f20] transition-all
                            {{ $isActiveFilter
                            ? 'rounded-[4px] border-black bg-white text-[#231f20]'
                            : 'rounded-[4px] border-[#f4f4f4] bg-[#f4f4f4] hover:border-gray-50 hover:shadow-[0_4px_12px_rgba(0,0,0,0.12)]' }}">
                        {{ $filterLabel }}
                        <span class="ml-1 text-[12px] font-normal text-[#8c8c8c]"
                            data-review-filter-count="{{ $filterKey }}">{{ $reviewCounts[$filterKey] }}</span>
                    </a>
                </div>
            @endforeach
        </nav>
    </div> 

    <form id="review-sort-form" method="GET" action="{{ route('admin.reviews.index') }}"
          class="mb-5 flex items-center gap-3 text-[14px]">
        <input type="hidden" name="filter" value="{{ $filter }}" data-review-sort-filter>
        <label for="review-sort" class="font-medium text-[#231f20]">Сортировать:</label>
        <select id="review-sort" name="sort" class="rounded-[6px] border border-gray-300 bg-white px-3 py-2"
                aria-label="Сортировка отзывов">
            <option value="date" @selected($sort === 'date')>По дате добавления</option>
            <option value="rating" @selected($sort === 'rating')>По рейтингу</option>
            <option value="useful" @selected($sort === 'useful')>По полезности</option>
        </select>
    </form>

    <div data-review-filter-results aria-live="polite">
        @include('admin.partials.admin-reviews-list')
    </div>
</div>

<div class="fixed inset-0 z-[1010] hidden items-center justify-center overflow-y-auto bg-[#231f20bf] p-4"
     role="dialog" aria-modal="true" aria-labelledby="admin-review-reply-title" data-admin-reply-modal>
    <div class="relative flex h-[327px] w-[404px] max-w-full flex-col overflow-hidden rounded-[10px] bg-white py-8 pl-[50px] pr-10 text-left">
        <button type="button" data-admin-reply-close aria-label="Закрыть окно"
                class="absolute right-[10px] top-[10px] flex h-[31px] w-[31px] cursor-pointer items-center justify-center text-[#231f20] hover:text-[#dc092e]">
            <svg class="h-[26.6px] w-[26.6px]" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                <path d="M3 3l14 14M17 3L3 17" stroke="currentColor" stroke-width="1.5"/>
            </svg>
        </button>
        <div class="flex h-full flex-col gap-6">
            <h2 id="admin-review-reply-title" class="pr-8 text-left text-[20px] font-bold leading-tight text-black"
                data-admin-reply-title>Ответ на отзыв</h2>
            <form method="POST" action="" class="flex min-h-0 flex-1 flex-col" data-admin-reply-form>
                @csrf
                <input type="hidden" name="_method" value="POST" data-admin-reply-method>
                <label for="admin-review-reply-body" class="mb-[5px] block pl-px text-left text-[13px] font-semibold text-black">
                    Ответ на отзыв<span class="text-[#DC092E]">*</span>
                </label>
                <textarea id="admin-review-reply-body" name="body" rows="3" maxlength="10000" required
                          class="mb-4 block min-h-0 w-full flex-1 resize-none rounded-[10px] border border-[#8c8c8c] bg-white px-4 pt-[14px] pb-[12px] text-[14px] leading-5 text-black outline-none focus:border-[#555]"
                          data-admin-reply-body></textarea>
                <button type="submit"
                        class="block w-full cursor-pointer rounded-[10px] border border-[#DC092E] bg-[#DC092E] px-5 py-2 text-center text-[15px] font-semibold text-white transition-colors duration-200 hover:bg-[#a80723] focus:bg-[#a80723]">
                    Сохранить
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
