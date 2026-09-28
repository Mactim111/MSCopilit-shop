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

        if (Review::where('user_id', $user->id)
            ->where('product_variant_id', $variant->id)
            ->exists()) {
            return $this->errorResponse(
                $request,
                'Вы уже оставляли отзыв на этот вариант товара.',
                422
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

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Спасибо! Отзыв отправлен на модерацию.',
                'review_id' => $review->id,
            ], 201);
        }

        return back()->with('success', 'Спасибо! Отзыв отправлен на модерацию.');
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
