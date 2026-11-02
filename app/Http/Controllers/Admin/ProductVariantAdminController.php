<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductVariantImage;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductAdminController extends Controller
{
    // public function index()
    // {
    //     $products = Product::withTrashed()->with(['category', 'images'])->paginate(20);
    //     return view('admin.products.index', compact('products'));
    // }

    public function index(Request $request)
    {
        $query = ProductVariant::withTrashed()->with(['category', 'images']);


        // Поиск
        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        // Фильтр по категории
        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }

        // Фильтр по наличию
        if ($request->filled('stock')) {
            if ($request->stock === 'in') {
                $query->where('stock', '>', 0);
            } elseif ($request->stock === 'out') {
                $query->where('stock', '=', 0);
            }
        }

        // Фильтр по цене
        if ($request->filled('price_min')) {
            $query->where('price', '>=', $request->price_min);
        }
        if ($request->filled('price_max')) {
            $query->where('price', '<=', $request->price_max);
        }

        // Сортировка
        $sort = $request->get('sort', 'id');
        $dir  = $request->get('dir', 'asc');

        $query->orderBy($sort, $dir);

        return view('admin.variants.index', [
            'variants'   => $query->paginate(20)->withQueryString(),
            'categories' => Category::all(),
            'sort'       => $sort,
            'dir'        => $dir,
        ]);
    }


    public function create()
    {
        return view('admin.variants.create', [
            'categories' => Category::all()
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title'       => 'required',
            'description' => 'nullable',
            'price'       => 'required|numeric',
            'stock'       => 'required|integer',
            'category_id' => 'nullable|exists:categories,id',
            'image'       => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'images.*'    => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Создаём товар
        $slugBase = Str::slug($data['title']);
        $slug = $slugBase . '-' . rand(10000, 99999);

        $data['slug'] = $slug;
        $variant = ProductVariant::create($data);

        // Главное изображение
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('products/main', 'public');
            $variant->update(['image' => $path]);
        }

        // Галерея
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                $path = $file->store('products/gallery', 'public');
                $variant->images()->create(['path' => $path]);
            }
        }

        return redirect()->route('admin.variants.index')->with('success', 'Товар создан');
    }

    public function edit(ProductVariant $variant)
    {
        return view('admin.variants.edit', [
            'variant'    => $variant,
            'categories' => Category::all()
        ]);
    }

    public function update(Request $request, Product $variant)
    {
        $data = $request->validate([
            'title'       => 'required',
            'description' => 'nullable',
            'price'       => 'required|numeric',
            'stock'       => 'required|integer',
            'category_id' => 'nullable|exists:categories,id',
            'image'       => 'mimes:jpeg,png,jpg,gif|max:2048',
            'images.*'    => 'mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($data['title'] !== $variant->title) {
            $slugBase = Str::slug($data['title']);
            $slug = $slugBase . '-' . rand(10000, 99999);
            $data['slug'] = $slug;
        }

        $variant->update($data);

        // Главное изображение
        if ($request->hasFile('image')) {
            if ($variant->image) {
                Storage::disk('public')->delete($variant->image);
            }
            // $data['image'] = $request->file('image')->store('products', 'public');
            $path = $request->file('image')->store('products/main', 'public');
            $variant->update(['image' => $path]);
        }

        // Удаление галереи
        if ($request->filled('remove_gallery')) {
            foreach ($request->remove_gallery as $imgId) {
                $img = $variant->images()->find($imgId);
                if ($img) {
                    Storage::disk('public')->delete($img->path);
                    $img->delete();
                }
            }
        }

        // Добавление новых изображений
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                $path = $file->store('products/gallery', 'public');
                $variant->images()->create(['path' => $path]);
            }
        }

        return redirect()->route('admin.variants.edit', $variant)->with('success', 'Товар обновлён');
    }

    // public function destroy(Product $variant)
    // {
    //     // if ($variant->image) {
        //     Storage::disk('public')->delete($variant->image);
        // }
    //     $variant->delete();
    //     return back()->with('success', 'Товар удалён');
    // }

    public function destroy(ProductVariant $variant)
    {
        $variant->delete();

        return response()->json(['status' => 'deleted']);
    }

    public function restore($slug)
    {
        $variant = ProductVariant::withTrashed()
            ->where('slug', $slug)
            ->firstOrFail();
        $variant->restore();
        return response()->json(['status' => 'restored']);
    }

//    public function deleteImage(ProductVariantImage $image)
//    {
//        Storage::disk('public')->delete($image->path);
//        $image->delete();
//
//        return back()->with('success', 'Изображение удалено');
//    }

    public function forceDelete($slug)
    {
        $variant = ProductVariant::withTrashed()
            ->where('slug', $slug)
            ->firstOrFail();

        // Удаляем главное изображение
        if ($variant->image) {
            Storage::disk('public')->delete($variant->image);
        }

        // Удаляем изображения галереи
        foreach ($variant->images as $img) {
            Storage::disk('public')->delete($img->path);
        }

        // Удаляем товар окончательно
        $variant->forceDelete();

        return response()->json(['status' => 'forceDeleted']);
    }

}
