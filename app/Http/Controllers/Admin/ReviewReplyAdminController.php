<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Models\ReviewReply;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ReviewReplyAdminController extends Controller
{
    public function store(Request $request, Review $review): RedirectResponse
    {
        $data = $request->validate([
            'body' => ['required', 'string', 'max:10000'],
        ]);

        DB::transaction(function () use ($request, $review, $data): void {
            $lockedReview = Review::query()->whereKey($review->id)->lockForUpdate()->firstOrFail();
            $existingReply = ReviewReply::withTrashed()
                ->where('review_id', $lockedReview->id)
                ->lockForUpdate()
                ->first();
            abort_if($existingReply !== null, 409, 'У отзыва уже есть ответ. Используйте действия в блоке ответа.');

            $lockedReview->reply()->create([
                'admin_id' => $request->user()->id,
                'body' => $data['body'],
            ]);
        });

        return back()->with('success', 'Ответ на отзыв добавлен.');
    }

    public function update(Request $request, Review $review): RedirectResponse
    {
        $data = $request->validate([
            'body' => ['required', 'string', 'max:10000'],
        ]);

        DB::transaction(function () use ($request, $review, $data): void {
            $lockedReview = Review::query()->whereKey($review->id)->lockForUpdate()->firstOrFail();
            $reply = $lockedReview->reply()->lockForUpdate()->firstOrFail();
            $reply->update([
                'admin_id' => $request->user()->id,
                'body' => $data['body'],
            ]);
        });

        return back()->with('success', 'Ответ на отзыв изменён.');
    }

    public function destroy(Review $review): RedirectResponse
    {
        DB::transaction(function () use ($review): void {
            $lockedReview = Review::query()->whereKey($review->id)->lockForUpdate()->firstOrFail();
            $reply = $lockedReview->reply()->lockForUpdate()->firstOrFail();
            $reply->delete();
        });

        return back()->with('success', 'Ответ на отзыв удалён.');
    }

    public function restore(Review $review): RedirectResponse
    {
        DB::transaction(function () use ($review): void {
            $lockedReview = Review::query()->whereKey($review->id)->lockForUpdate()->firstOrFail();
            $reply = ReviewReply::onlyTrashed()
                ->where('review_id', $lockedReview->id)
                ->lockForUpdate()
                ->firstOrFail();
            $reply->restore();
        });

        return back()->with('success', 'Ответ на отзыв восстановлен.');
    }
}
