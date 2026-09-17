<?php

namespace App\Http\Controllers;

use App\Exceptions\FavoriteLimitExceededException;
use App\Models\Favorite;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FavoriteController extends Controller
{
    public function index(): View
    {
        if (!auth()->check()) {
            return view('profile.favorites-guest');
        }

        $favorites = auth()->user()
            ->favoriteVariants()
            ->with(['product.category.parent.parent', 'images', 'labels'])
            ->latest('favorites.created_at')
            ->paginate(20);

        return view('profile.favorites', compact('favorites'));
    }

    public function toggle(Request $request, ProductVariant $variant): JsonResponse|RedirectResponse
    {
        try {
            [$isFavorite, $message, $icon] = DB::transaction(function () use ($request, $variant) {
                $favorite = Favorite::where('user_id', $request->user()->id)
                    ->where('product_variant_id', $variant->id)
                    ->first();

                if ($favorite) {
                    $favorite->delete();

                    return [
                        false,
                        'Товар удалён из избранного',
                        view('products.icons.heart-outline')->render(),
                    ];
                }

                if ($request->user()->favorites()->count() >= 200) {
                    throw new FavoriteLimitExceededException();
                }

                Favorite::create([
                    'user_id' => $request->user()->id,
                    'product_variant_id' => $variant->id,
                ]);

                return [
                    true,
                    'Товар добавлен в избранное',
                    view('products.icons.heart-filled')->render(),
                ];
            });
        } catch (FavoriteLimitExceededException $exception) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => $exception->getMessage(),
                    'count' => $request->user()->favorites()->count(),
                ], 422);
            }

            return back()->with('error', $exception->getMessage());
        }

        $count = $request->user()->favorites()->count();

        if ($request->expectsJson()) {
            return response()->json([
                'is_favorite' => $isFavorite,
                'message' => $message,
                'icon' => $icon,
                'count' => $count,
            ]);
        }

        return back()->with('success', $message);
    }
}
