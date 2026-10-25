<?php

namespace App\Observers;

use App\Models\Review;
use InvalidArgumentException;

class ReviewObserver
{
    public function saving(Review $review): void
    {
        if (! $review->exists) {
            if ($review->is_published) {
                $review->moderation_status = Review::MODERATION_APPROVED;
            } elseif ($review->moderation_status === Review::MODERATION_APPROVED) {
                $review->is_published = true;
            }
        }

        if (! in_array($review->moderation_status, Review::MODERATION_STATUSES, true)) {
            throw new InvalidArgumentException('Invalid review moderation status.');
        }

        if (
            $review->addition_moderation_status !== null
            && ! in_array($review->addition_moderation_status, Review::MODERATION_STATUSES, true)
        ) {
            throw new InvalidArgumentException('Invalid review addition moderation status.');
        }

        if ($review->exists) {
            if ($review->isDirty('moderation_status')) {
                $review->is_published = $review->moderation_status === Review::MODERATION_APPROVED;
            } elseif ($review->isDirty('is_published')) {
                $review->moderation_status = $review->is_published
                    ? Review::MODERATION_APPROVED
                    : Review::MODERATION_PENDING;
            }

            if ($review->isDirty('addition_moderation_status')) {
                $review->addition_is_published =
                    $review->addition_moderation_status === Review::MODERATION_APPROVED;
            } elseif ($review->isDirty('addition_is_published')) {
                $review->addition_moderation_status = $review->addition_is_published
                    ? Review::MODERATION_APPROVED
                    : Review::MODERATION_PENDING;
            }
        } elseif ($review->addition_moderation_status !== null) {
            $review->addition_is_published =
                $review->addition_moderation_status === Review::MODERATION_APPROVED;
        }
    }

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
