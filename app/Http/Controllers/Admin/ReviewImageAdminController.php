<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Models\ReviewImage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Throwable;

class ReviewImageAdminController extends Controller
{
    public function store(Request $request, Review $review): RedirectResponse
    {
        $data = $request->validate([
            'photo' => ['required', 'image', 'max:5120'],
        ]);
        $storedPath = null;

        try {
            DB::transaction(function () use ($review, $data, &$storedPath) {
                $lockedReview = Review::query()->whereKey($review->id)->lockForUpdate()->firstOrFail();
                $positions = $lockedReview->images()->pluck('position');
                $position = collect(range(1, 6))
                    ->first(fn (int $candidate) => ! $positions->contains($candidate));

                if ($position === null) {
                    abort(422, 'К отзыву можно прикрепить не более шести фотографий.');
                }

                $storedPath = $data['photo']->store('reviews', 'public');
                $lockedReview->images()->create([
                    'path' => $storedPath,
                    'position' => $position + 1,
                ]);
            });
        } catch (Throwable $exception) {
            if ($storedPath) {
                Storage::disk('public')->delete($storedPath);
            }

            throw $exception;
        }

        return back()->with('success', 'Фотография добавлена к отзыву.');
    }

    public function update(Request $request, Review $review, ReviewImage $image): RedirectResponse
    {
        $data = $request->validate([
            'photo' => ['required', 'image', 'max:5120'],
        ]);

        $image = $review->images()->findOrFail($image->id);
        $oldPath = $image->path;
        $newPath = $data['photo']->store('reviews', 'public');

        $image->update(['path' => $newPath]);
        Storage::disk('public')->delete($oldPath);

        return back()->with('success', 'Фотография отзыва заменена.');
    }

    public function destroy(Review $review, ReviewImage $image): RedirectResponse
    {
        $image = $review->images()->findOrFail($image->id);
        $path = $image->path;

        $image->delete();
        Storage::disk('public')->delete($path);

        return back()->with('success', 'Фотография отзыва удалена.');
    }
}
