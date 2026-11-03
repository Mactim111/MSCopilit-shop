<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class ReviewModerationAdminController extends Controller
{
    private const FILTERS = [
        'all',
        'pending',
        'approved',
        'rejected',
        'with_addition',
        'with_reply',
        'deleted',
    ];

    public function index(Request $request)
    {
        $data = $request->validate([
            'filter' => ['sometimes', Rule::in(self::FILTERS)],
            'sort' => ['sometimes', Rule::in(['date', 'rating', 'useful'])],
        ]);
        $filter = $data['filter'] ?? 'all';
        $sort = $data['sort'] ?? 'date';
        $activeReviews = Review::query();

        $reviewCounts = [
            'all' => (clone $activeReviews)->count(),
            'pending' => (clone $activeReviews)
                ->where('moderation_status', Review::MODERATION_PENDING)
                ->count(),
            'approved' => (clone $activeReviews)
                ->where('moderation_status', Review::MODERATION_APPROVED)
                ->count(),
            'rejected' => (clone $activeReviews)
                ->where('moderation_status', Review::MODERATION_REJECTED)
                ->count(),
            'with_addition' => (clone $activeReviews)
                ->whereNotNull('addition')
                ->whereNull('addition_deleted_at')
                ->count(),
            'with_reply' => (clone $activeReviews)
                ->whereHas('reply')
                ->count(),
            'deleted' => Review::onlyTrashed()->count(),
        ];

        $reviewsQuery = $filter === 'deleted'
            ? Review::onlyTrashed()
            : Review::query();
        $reviewsQuery->with([
            'user',
            'variant.images',
            'order',
            'images' => fn ($images) => $images->withTrashed(),
            'reply' => fn ($reply) => $reply->withTrashed()->with('admin'),
        ])
            ->withCount([
                'votes as likes_count' => fn ($votes) => $votes->where('type', 'like'),
                'votes as dislikes_count' => fn ($votes) => $votes->where('type', 'dislike'),
            ]);

        match ($filter) {
            'pending' => $reviewsQuery->where('moderation_status', Review::MODERATION_PENDING),
            'approved' => $reviewsQuery->where('moderation_status', Review::MODERATION_APPROVED),
            'rejected' => $reviewsQuery->where('moderation_status', Review::MODERATION_REJECTED),
            'with_addition' => $reviewsQuery
                ->whereNotNull('addition')
                ->whereNull('addition_deleted_at'),
            'with_reply' => $reviewsQuery->whereHas('reply'),
            default => null,
        };

        match ($sort) {
            'rating' => $reviewsQuery->orderByDesc('rating'),
            'useful' => $reviewsQuery->orderByHelpfulness(),
            default => $reviewsQuery->orderByDesc('created_at'),
        };
        $reviews = $reviewsQuery
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        if ($request->expectsJson()) {
            return response()->json([
                'html' => view('admin.partials.admin-reviews-list', compact('reviews'))->render(),
                'counts' => $reviewCounts,
                'filter' => $filter,
                'sort' => $sort,
            ]);
        }

        return view('admin.reviews.index', compact('reviews', 'reviewCounts', 'filter', 'sort'));
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
            abort_unless($lockedReview->is_published, 422, 'Опубликуйте отзыв, прежде чем модерировать дополнение.');

            abort_if(
                $lockedReview->addition === null || $lockedReview->addition_deleted_at !== null,
                422,
                'У отзыва нет активного дополнения для модерации.'
            );

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

    public function destroy(Review $review): RedirectResponse
    {
        DB::transaction(function () use ($review): void {
            $lockedReview = Review::query()
                ->whereKey($review->id)
                ->lockForUpdate()
                ->firstOrFail();
            $lockedReview->delete();
        });

        return back()->with('success', 'Отзыв перемещён в удалённые.');
    }

    public function restore(int $review): RedirectResponse
    {
        DB::transaction(function () use ($review): void {
            $lockedReview = Review::onlyTrashed()
                ->whereKey($review)
                ->lockForUpdate()
                ->firstOrFail();

            $lockedReview->moderation_status = Review::MODERATION_PENDING;
            $lockedReview->is_published = false;
            if ($lockedReview->addition !== null && $lockedReview->addition_deleted_at === null) {
                $lockedReview->addition_moderation_status = Review::MODERATION_PENDING;
                $lockedReview->addition_is_published = false;
            }
            $lockedReview->restore();
        });

        return back()->with('success', 'Отзыв восстановлен и отправлен на модерацию.');
    }

    public function forceDelete(int $review): RedirectResponse
    {
        DB::transaction(function () use ($review): void {
            $lockedReview = Review::onlyTrashed()
                ->whereKey($review)
                ->lockForUpdate()
                ->firstOrFail();
            $paths = $lockedReview->images()
                ->withTrashed()
                ->pluck('path')
                ->filter(fn (string $path) => Str::startsWith($path, 'reviews/')
                    && Storage::disk('public')->exists($path))
                ->unique()
                ->all();

            foreach ($paths as $path) {
                if (! Storage::disk('public')->delete($path)) {
                    throw new RuntimeException('Не удалось удалить файлы удалённого отзыва из хранилища.');
                }
            }

            $lockedReview->forceDelete();
        });

        return back()->with('success', 'Отзыв окончательно удалён.');
    }

    public function deleteAddition(Review $review): RedirectResponse
    {
        DB::transaction(function () use ($review): void {
            $lockedReview = Review::query()->whereKey($review->id)->lockForUpdate()->firstOrFail();
            abort_unless($lockedReview->is_published, 422, 'Опубликуйте отзыв, прежде чем управлять дополнением.');
            abort_if(
                $lockedReview->addition === null || $lockedReview->addition_deleted_at !== null,
                404
            );
            $lockedReview->update([
                'addition_deleted_at' => now(),
                'addition_is_published' => false,
            ]);
        });

        return back()->with('success', 'Дополнение к отзыву перемещено в удалённые.');
    }

    public function restoreAddition(Review $review): RedirectResponse
    {
        DB::transaction(function () use ($review): void {
            $lockedReview = Review::query()->whereKey($review->id)->lockForUpdate()->firstOrFail();
            abort_unless($lockedReview->is_published, 422, 'Опубликуйте отзыв, прежде чем управлять дополнением.');
            abort_if(
                $lockedReview->addition === null || $lockedReview->addition_deleted_at === null,
                404
            );
            $lockedReview->update([
                'addition_deleted_at' => null,
                'addition_moderation_status' => Review::MODERATION_PENDING,
                'addition_is_published' => false,
            ]);
        });

        return back()->with('success', 'Дополнение восстановлено и отправлено на модерацию.');
    }
}
