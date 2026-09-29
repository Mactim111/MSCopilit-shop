<?php

namespace App\Http\Controllers;

use App\Models\ProductVariant;
use App\Models\Review;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Throwable;

class ReviewController extends Controller
{
    public function index(Request $request, ProductVariant $variant): JsonResponse
    {
        $filters = $request->validate([
            'scope' => ['sometimes', 'in:all,variant'],
            'offset' => ['sometimes', 'integer', 'min:0'],
        ]);

        $scope = $filters['scope'] ?? 'all';
        $offset = $filters['offset'] ?? 0;
        $query = $variant->product->publishedReviews()
            ->with(['user', 'variant', 'images', 'reply.admin'])
            ->withCount([
                'votes as likes_count' => fn ($votes) => $votes->where('type', 'like'),
                'votes as dislikes_count' => fn ($votes) => $votes->where('type', 'dislike'),
            ]);

        if ($scope === 'variant') {
            $query->where('reviews.product_variant_id', $variant->id);
        }

        if ($request->user()) {
            $query->with([
                'votes' => fn ($votes) => $votes->where('user_id', $request->user()->id),
            ]);
        }

        $total = (clone $query)->count();
        $reviews = $query
            ->latest('reviews.created_at')
            ->orderByDesc('reviews.id')
            ->offset($offset)
            ->limit(5)
            ->get();

        return response()->json([
            'html' => view('variants.partials.review-cards', [
                'reviews' => $reviews,
                'isAuthenticated' => $request->user() !== null,
            ])->render(),
            'total' => $total,
            'loaded' => min($offset + $reviews->count(), $total),
            'has_more' => $offset + $reviews->count() < $total,
            'next_offset' => $offset + $reviews->count(),
            'variant_count' => $variant->product->publishedReviews()
                ->where('reviews.product_variant_id', $variant->id)
                ->count(),
            'scope' => $scope,
        ]);
    }

    public function store(Request $request, ProductVariant $variant): JsonResponse|RedirectResponse
    {
        $user = $request->user();

        $hasPurchasedVariant = $user->orders()
            ->whereIn('status', ['paid', 'shipped'])
            ->whereHas('items', fn ($query) => $query->where('product_variant_id', $variant->id))
            ->exists();

        if (! $hasPurchasedVariant) {
            return $this->errorResponse(
                $request,
                'Оставлять отзывы могут только пользователи, купившие этот товар.',
                403
            );
        }

        $data = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'advantages' => ['nullable', 'string', 'max:5000'],
            'disadvantages' => ['nullable', 'string', 'max:5000'],
            'comment' => ['required', 'string', 'max:10000'],
            'photos' => ['nullable', 'array', 'max:6'],
            'photos.*' => ['image', 'max:5120'],
        ]);

        $photos = $data['photos'] ?? [];
        unset($data['photos']);
        $storedPaths = [];

        try {
            $review = DB::transaction(function () use ($user, $variant, $data, $photos, &$storedPaths) {
                $user->newQuery()
                    ->whereKey($user->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($user->reviews()
                    ->where('product_variant_id', $variant->id)
                    ->exists()) {
                    return null;
                }

                $review = $user->reviews()->create([
                    ...$data,
                    'product_variant_id' => $variant->id,
                    'is_published' => false,
                ]);

                foreach ($photos as $position => $photo) {
                    $path = $photo->store('reviews', 'public');
                    $storedPaths[] = $path;
                    $review->images()->create([
                        'path' => $path,
                        'position' => $position + 1,
                    ]);
                }

                return $review;
            });
        } catch (Throwable $exception) {
            Storage::disk('public')->delete($storedPaths);
            throw $exception;
        }

        if ($review === null) {
            Storage::disk('public')->delete($storedPaths);

            return $this->errorResponse(
                $request,
                'У вас уже есть активный отзыв на этот вариант товара.',
                422
            );
        }

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Спасибо! Отзыв отправлен на модерацию.',
                'review_id' => $review->id,
            ], 201);
        }

        return back()->with('success', 'Спасибо! Отзыв отправлен на модерацию.');
    }

    public function addAddition(Request $request, Review $review): JsonResponse|RedirectResponse
    {
        $data = $request->validate([
            'addition' => ['required', 'string', 'max:10000'],
        ]);

        $wasAdded = DB::transaction(function () use ($request, $review, $data) {
            $lockedReview = Review::query()
                ->whereKey($review->id)
                ->where('user_id', $request->user()->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedReview->addition !== null) {
                return false;
            }

            $lockedReview->update([
                'addition' => $data['addition'],
                'addition_updated_at' => now(),
                'addition_is_published' => false,
            ]);

            return true;
        });

        if (! $wasAdded) {
            return $this->errorResponse(
                $request,
                'Вы уже дополняли этот отзыв.',
                422
            );
        }

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Дополнение отправлено на модерацию.',
                'review_id' => $review->id,
            ], 200);
        }

        return back()->with('success', 'Дополнение отправлено на модерацию.');
    }

    public function destroy(Request $request, Review $review): JsonResponse|RedirectResponse
    {
        $deleted = Review::query()
            ->whereKey($review->id)
            ->where('user_id', $request->user()->id)
            ->delete();

        abort_unless($deleted > 0, 404);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Отзыв удалён.',
                'review_id' => $review->id,
            ]);
        }

        return back()->with('success', 'Отзыв удалён.');
    }

    private function errorResponse(
        Request $request,
        string $message,
        int $status
    ): JsonResponse|RedirectResponse {
        if ($request->expectsJson()) {
            return response()->json(['message' => $message], $status);
        }

        return back()->with('error', $message);
    }
}
