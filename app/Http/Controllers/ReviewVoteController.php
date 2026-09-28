<?php

namespace App\Http\Controllers;

use App\Models\Review;
use App\Models\ReviewVote;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReviewVoteController extends Controller
{
    public function toggle(Request $request, Review $review): JsonResponse
    {
        $data = $request->validate([
            'type' => ['required', 'in:like,dislike'],
        ]);

        $user = $request->user();

        $currentType = DB::transaction(function () use ($review, $user, $data) {
            $lockedReview = Review::query()
                ->whereKey($review->id)
                ->where('is_published', true)
                ->lockForUpdate()
                ->firstOrFail();

            $vote = $lockedReview->votes()
                ->where('user_id', $user->id)
                ->lockForUpdate()
                ->first();

            if ($vote?->type === $data['type']) {
                $vote->delete();

                return null;
            }

            if ($vote) {
                $vote->update(['type' => $data['type']]);
            } else {
                $lockedReview->votes()->create([
                    'user_id' => $user->id,
                    'type' => $data['type'],
                ]);
            }

            return $data['type'];
        });

        return response()->json([
            'like_count' => $review->votes()->where('type', ReviewVote::TYPE_LIKE)->count(),
            'dislike_count' => $review->votes()->where('type', ReviewVote::TYPE_DISLIKE)->count(),
            'user_vote' => $currentType,
        ]);
    }
}
