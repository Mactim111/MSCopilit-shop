<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Review;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ReviewModerationAdminController extends Controller
{
    
    public function index()
    {
        $data = request()->validate([
            'filter' => ['sometimes', Rule::in(['all', 'approved', 'rejected'])],
        ]);
        $filter = $data['filter'] ?? 'all';
        $reviewsAll = Review::all();

        $reviewCounts = [
            'all' => (clone $reviewsAll)->count(),
            'approved' => (clone $reviewsAll)
                ->where('moderation_status', Review::MODERATION_APPROVED)
                ->count(),
            'rejected' => (clone $reviewsAll)
                ->where('moderation_status', Review::MODERATION_REJECTED)
                ->count(),
        ];

        $reviewsQuery = $reviewsAll
            ->with(['variant.images', 'images', 'reply.admin'])
            ->withCount([
                'votes as likes_count' => fn ($votes) => $votes->where('type', 'like'),
                'votes as dislikes_count' => fn ($votes) => $votes->where('type', 'dislike'),
            ]);
        if ($filter !== 'all') {
            $status = $filter === 'approved'
                ? Review::MODERATION_APPROVED
                : Review::MODERATION_REJECTED;
            $reviewsQuery->where('moderation_status', $status);
        }

        $reviews = $reviewsQuery->latest()->get();

        if (request()->expectsJson()) {
            return response()->json([
                'html' => view('admin.partials.admin-reviews-list', compact('reviews', 'filter'))->render(),
                'counts' => $reviewCounts,
                'filter' => $filter,
            ]);
        }

        return view('admin.reviews.index', compact('reviews', 'reviewCounts', 'filter'));
    }
    
    public function update(Request $request, Review $review): JsonResponse|RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(Review::MODERATION_STATUSES)],
        ]);

        $review = DB::transaction(function () use ($review, $data): Review {
            $lockedReview = Review::query()
                ->whereKey($review->id)
                ->lockForUpdate()
                ->firstOrFail();

            $lockedReview->update(['moderation_status' => $data['status']]);

            return $lockedReview;
        });

        if ($request->expectsJson()) {
            return response()->json([
                'review_id' => $review->id,
                'status' => $review->moderation_status,
                'is_published' => $review->is_published,
            ]);
        }

        return back()->with('success', 'Статус отзыва обновлён.');
    }

    public function updateAddition(Request $request, Review $review): JsonResponse|RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(Review::MODERATION_STATUSES)],
        ]);

        $review = DB::transaction(function () use ($review, $data): Review {
            $lockedReview = Review::query()
                ->whereKey($review->id)
                ->lockForUpdate()
                ->firstOrFail();

            abort_if($lockedReview->addition === null, 422, 'У отзыва нет дополнения для модерации.');

            $lockedReview->update(['addition_moderation_status' => $data['status']]);

            return $lockedReview;
        });

        if ($request->expectsJson()) {
            return response()->json([
                'review_id' => $review->id,
                'status' => $review->addition_moderation_status,
                'is_published' => $review->addition_is_published,
            ]);
        }

        return back()->with('success', 'Статус дополнения к отзыву обновлён.');
    }
}
