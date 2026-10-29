<div id="reviews-list" class="space-y-4">
    @forelse($reviews as $review)
        <x-profile-review-card :review="$review" />
    @empty
        <div class="text-left">
            <p class="text-[15px] font-semibold text-black -mt-4">
                Вы ещё не оставляли отзывы на товары
            </p>
        </div>
    @endforelse
</div>