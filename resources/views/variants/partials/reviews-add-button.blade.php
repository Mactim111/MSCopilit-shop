@php
    $buttonWidth = $buttonWidth ?? 'w-[152px]';
@endphp

@if($isReviewRestricted)
    <button type="button"
            disabled
            class="block h-[40px] {{ $buttonWidth }} cursor-not-allowed rounded-lg border border-[#e0e0e0] bg-[#e0e0e0] px-4 text-[15px] font-semibold text-white">
        Добавить отзыв
    </button>
@elseif($hasActiveReview ?? false)
    <button type="button"
            disabled
            class="block h-[40px] {{ $buttonWidth }} cursor-not-allowed rounded-lg border border-[#e0e0e0] bg-[#e0e0e0] px-4 text-[15px] font-semibold text-white">
        Вы уже оставили отзыв
    </button>
@else
    @auth
    <button type="button"
            data-review-form="create"
            data-form-action="{{ route('reviews.store', $variant) }}"
            data-product-title="{{ $variant->title }}"
            data-product-image="{{ $variant->mainImage() }}"
            class="block h-[40px] {{ $buttonWidth }} rounded-lg border border-red-600 bg-white px-4 text-[15px] font-semibold text-red-600 hover:bg-red-600 hover:text-white">
        Добавить отзыв
    </button>
    @else
        <button type="button"
                data-review-action
                class="block h-[40px] {{ $buttonWidth }} rounded-lg border border-red-600 bg-white px-4 text-[15px] font-semibold text-red-600 hover:bg-red-600 hover:text-white">
            Добавить отзыв
        </button>
    @endauth
@endif
