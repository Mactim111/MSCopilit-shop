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

        $wasRestored = DB::transaction(function () use ($request, $review, $data): bool {
            $lockedReview = Review::query()->whereKey($review->id)->lockForUpdate()->firstOrFail();
            abort_unless($lockedReview->is_published, 422, 'Опубликуйте отзыв, прежде чем добавлять ответ.');
            $reply = ReviewReply::withTrashed()
                ->where('review_id', $lockedReview->id)
                ->lockForUpdate()
                ->first();

            if ($reply) {
                abort_unless($reply->trashed(), 409, 'У отзыва уже есть активный ответ.');
                $reply->admin_id = $request->user()->id;
                $reply->body = $data['body'];
                $reply->restore();

                return true;
            } else {
                $lockedReview->reply()->create([
                    'admin_id' => $request->user()->id,
                    'body' => $data['body'],
                ]);

                return false;
            }
        });

        return back()->with('success', $wasRestored
            ? 'Ответ на отзыв восстановлен.'
            : 'Ответ на отзыв добавлен.');
    }

    public function update(Request $request, Review $review): RedirectResponse
    {
        $data = $request->validate([
            'body' => ['required', 'string', 'max:10000'],
        ]);

        DB::transaction(function () use ($request, $review, $data): void {
            $lockedReview = Review::query()->whereKey($review->id)->lockForUpdate()->firstOrFail();
            abort_unless($lockedReview->is_published, 422, 'Опубликуйте отзыв, прежде чем изменять ответ.');
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
            abort_unless($lockedReview->is_published, 422, 'Опубликуйте отзыв, прежде чем удалять ответ.');
            $reply = $lockedReview->reply()->lockForUpdate()->firstOrFail();
            $reply->delete();
        });

        return back()->with('success', 'Ответ на отзыв удалён.');
    }

    public function restore(Review $review): RedirectResponse
    {
        DB::transaction(function () use ($review): void {
            $lockedReview = Review::query()->whereKey($review->id)->lockForUpdate()->firstOrFail();
            abort_unless($lockedReview->is_published, 422, 'Опубликуйте отзыв, прежде чем восстанавливать ответ.');
            $reply = ReviewReply::onlyTrashed()
                ->where('review_id', $lockedReview->id)
                ->lockForUpdate()
                ->firstOrFail();
            $reply->restore();
        });

        return back()->with('success', 'Ответ на отзыв восстановлен.');
    }
}
