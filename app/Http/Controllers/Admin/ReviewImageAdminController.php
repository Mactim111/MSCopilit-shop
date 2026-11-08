<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Models\ReviewImage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
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
                if (! $storedPath) {
                    throw new RuntimeException('Не удалось сохранить файл фотографии отзыва.');
                }

                $lockedReview->images()->create([
                    'path' => $storedPath,
                    'position' => $position,
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
        if (! $newPath) {
            throw new RuntimeException('Не удалось сохранить замену фотографии отзыва.');
        }

        try {
            if (! $image->update(['path' => $newPath])) {
                throw new RuntimeException('Не удалось обновить запись фотографии отзыва.');
            }
        } catch (Throwable $exception) {
            Storage::disk('public')->delete($newPath);
            throw $exception;
        }

        if (Str::startsWith($oldPath, 'reviews/')
            && Storage::disk('public')->exists($oldPath)
            && ! Storage::disk('public')->delete($oldPath)) {
            throw new RuntimeException('Фотография заменена, но старый файл не удалось удалить из хранилища.');
        }

        return back()->with('success', 'Фотография отзыва заменена.');
    }

    public function destroy(Review $review, ReviewImage $image): RedirectResponse
    {
        $image = $review->images()->findOrFail($image->id);
        $image->delete();

        $mediaLabel = $image->media_type === 'video' ? 'Видео' : 'Фото';

        return back()->with('success', "{$mediaLabel} отзыва удалено.");
    }

    public function restore(Review $review, int $image): RedirectResponse
    {
        $mediaType = DB::transaction(function () use ($review, $image): string {
            $lockedReview = Review::query()->whereKey($review->id)->lockForUpdate()->firstOrFail();
            $deletedImage = ReviewImage::onlyTrashed()
                ->where('review_id', $lockedReview->id)
                ->whereKey($image)
                ->lockForUpdate()
                ->firstOrFail();
            $occupiedPositions = $lockedReview->images()->pluck('position');
            $position = collect(range(1, 6))
                ->first(fn (int $candidate) => ! $occupiedPositions->contains($candidate));

            abort_if($position === null, 422, 'Нет свободного места для восстановления медиафайла.');
            abort_unless(
                Storage::disk('public')->exists($deletedImage->path),
                422,
                'Файл медиафайла отсутствует в хранилище и не может быть восстановлен.'
            );

            $deletedImage->position = $position;
            $deletedImage->restore();

            return $deletedImage->media_type;
        });

        $mediaLabel = $mediaType === 'video' ? 'Видео' : 'Фото';

        return back()->with('success', "{$mediaLabel} отзыва восстановлено.");
    }
}
