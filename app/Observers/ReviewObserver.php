<?php

namespace App\Observers;

use App\Models\Review;

class ReviewObserver
{
    public function created(Review $review): void
    {
        if ($review->is_published) {
            $this->refreshProductRating($review);
        }
    }

    public function updated(Review $review): void
    {
        if ($review->wasChanged(['rating', 'is_published', 'product_variant_id'])) {
            $this->refreshProductRating($review);
        }
    }

    public function deleted(Review $review): void
    {
        $this->refreshProductRating($review);
    }

    public function restored(Review $review): void
    {
        $this->refreshProductRating($review);
    }

    private function refreshProductRating(Review $review): void
    {
        $product = $review->variant?->product;

        if (! $product) {
            return;
        }

        $stats = $product->publishedReviewStats();

        $product->forceFill([
            'rating' => $stats['rating'],
            'reviews_count' => $stats['count'],
        ])->saveQuietly();
    }
}
