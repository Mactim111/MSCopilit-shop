<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ReviewReplyAdminController extends Controller
{
    public function store(Request $request, Review $review): RedirectResponse
    {
        $data = $request->validate([
            'body' => ['required', 'string', 'max:10000'],
        ]);

        $review->reply()->updateOrCreate([], [
            'admin_id' => $request->user()->id,
            'body' => $data['body'],
        ]);

        return back()->with('success', 'Ответ магазина сохранён.');
    }

    public function update(Request $request, Review $review): RedirectResponse
    {
        return $this->store($request, $review);
    }

    public function destroy(Review $review): RedirectResponse
    {
        $review->reply()->delete();

        return back()->with('success', 'Ответ магазина удалён.');
    }
}
