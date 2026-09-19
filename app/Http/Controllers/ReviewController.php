<?php

namespace App\Http\Controllers;

use App\Models\ProductVariant;
use App\Models\Review;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
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
        ]);

        $review = $user->reviews()->create([
            ...$data,
            'product_variant_id' => $variant->id,
            'is_published' => false,
        ]);

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
