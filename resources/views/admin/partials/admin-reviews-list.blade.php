<div id="reviews-list" class="space-y-4">
    @forelse($reviews as $review)
        <x-admin-review-card :review="$review" />
    @empty
        <div class="text-left">
            <p class="text-[15px] font-semibold text-black -mt-4">
                Отзывы на товары отсутствуют
            </p>
        </div>
    @endforelse
</div>
