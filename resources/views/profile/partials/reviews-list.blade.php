<div id="reviews-list" class="space-y-4">
    @forelse($reviews as $review)
        <x-review-card :review="$review" />
    @empty
        <div class="text-left">
            <p class="text-[15px] font-semibold text-black">
                Вы ещё не оставляли отзывы на товары
            </p>
        </div>
    @endforelse
</div>