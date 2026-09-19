<?php

namespace App\Http\Controllers;

use App\Exceptions\FavoriteLimitExceededException;
use App\Models\Category;
use App\Models\Favorite;
use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FavoriteController extends Controller
{
    public function index(Request $request): View
    {
        if (!auth()->check()) {
            return view('profile.favorites-guest');
        }

        $categoryId = $request->integer('subcategory') ?: null;

        $favoriteVariantsQuery = auth()->user()
            ->favoriteVariants()
            ->with(['product.category.parent.parent', 'images', 'labels']);

        if ($categoryId) {
            $favoriteVariantsQuery->whereHas('product', function (Builder $query) use ($categoryId) {
                $query->where('category_id', $categoryId);
            });
        }

        $favorites = $favoriteVariantsQuery
            ->latest('favorites.created_at')
            ->paginate(20)
            ->withQueryString();

        /*
         * Для агрегирования используем query builder напрямую.
         * BelongsToMany автоматически добавляет pivot-колонки favorites
         * в SELECT, из-за чего MariaDB в строгом режиме требует добавить
         * их в GROUP BY и отклоняет запрос.
         */
        $favoriteTags = DB::table('favorites')
            ->join('product_variants', 'product_variants.id', '=', 'favorites.product_variant_id')
            ->join('products', 'products.id', '=', 'product_variants.product_id')
            ->join('categories', 'categories.id', '=', 'products.category_id')
            ->where('favorites.user_id', auth()->id())
            ->select('categories.id', 'categories.title', 'categories.slug')
            ->selectRaw('COUNT(favorites.id) as favorite_count')
            ->groupBy('categories.id', 'categories.title', 'categories.slug')
            ->orderBy('categories.title')
            ->get();

        return view('profile.favorites', compact(
            'favorites',
            'favoriteTags',
            'categoryId'
        ));
    }

    public function deleteCategory(Request $request, Category $category): JsonResponse|RedirectResponse
    {
        $deleted = Favorite::where('user_id', $request->user()->id)
            ->whereIn(
                'product_variant_id',
                ProductVariant::withTrashed()
                    ->whereHas('product', fn (Builder $query) => $query->where('category_id', $category->id))
                    ->select('id')
            )
            ->delete();

        $message = $deleted > 0
            ? "Избранные товары из подкатегории «{$category->title}» удалены"
            : 'В избранном нет товаров этой подкатегории';

        if ($request->expectsJson()) {
            return response()->json([
                'message' => $message,
                'count' => $request->user()->favorites()->count(),
            ]);
        }

        return back()->with('success', $message);
    }

    public function deleteAll(Request $request): JsonResponse|RedirectResponse
    {
        $deleted = Favorite::where('user_id', $request->user()->id)->delete();
        $message = $deleted > 0
            ? 'Все товары удалены из избранного'
            : 'В избранном пока нет товаров';

        if ($request->expectsJson()) {
            return response()->json([
                'message' => $message,
                'count' => 0,
            ]);
        }

        return back()->with('success', $message);
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
