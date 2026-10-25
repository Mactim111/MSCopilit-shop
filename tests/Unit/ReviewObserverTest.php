<?php

namespace Tests\Unit;

use App\Models\Review;
use App\Observers\ReviewObserver;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class ReviewObserverTest extends TestCase
{
    public function test_it_synchronizes_review_and_addition_publication_separately(): void
    {
        $review = new Review([
            'moderation_status' => Review::MODERATION_APPROVED,
            'addition' => 'Текст дополнения',
            'addition_moderation_status' => Review::MODERATION_PENDING,
        ]);

        (new ReviewObserver())->saving($review);

        $this->assertTrue($review->is_published);
        $this->assertFalse($review->addition_is_published);
    }

    public function test_it_rejects_unknown_moderation_statuses(): void
    {
        $review = new Review([
            'moderation_status' => 'unknown',
        ]);

        $this->expectException(InvalidArgumentException::class);

        (new ReviewObserver())->saving($review);
    }
}
