<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use App\Models\ProductImage;
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
        $query = Product::withTrashed()->with(['category', 'images']);


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

        return view('admin.products.index', [
            'products'   => $query->paginate(20)->withQueryString(),
            'categories' => Category::all(),
            'sort'       => $sort,
            'dir'        => $dir,
        ]);
    }


    public function create()
    {
        return view('admin.products.create', [
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
        $product = Product::create($data);

        // Главное изображение
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('products/main', 'public');
            $product->update(['image' => $path]);
        }

        // Галерея
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                $path = $file->store('products/gallery', 'public');
                $product->images()->create(['path' => $path]);
            }
        }

        return redirect()->route('admin.products.index')->with('success', 'Товар создан');
    }

    public function edit(Product $product)
    {
        return view('admin.products.edit', [
            'product'    => $product,
            'categories' => Category::all()
        ]);
    }

    public function update(Request $request, Product $product)
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

        if ($data['title'] !== $product->title) {
            $slugBase = Str::slug($data['title']);
            $slug = $slugBase . '-' . rand(10000, 99999);
            $data['slug'] = $slug;
        }

        $product->update($data);

        // Главное изображение
        if ($request->hasFile('image')) {
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }
            // $data['image'] = $request->file('image')->store('products', 'public');
            $path = $request->file('image')->store('products/main', 'public');
            $product->update(['image' => $path]);
        }

        // Удаление галереи
        if ($request->filled('remove_gallery')) {
            foreach ($request->remove_gallery as $imgId) {
                $img = $product->images()->find($imgId);
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
                $product->images()->create(['path' => $path]);
            }
        }

        return redirect()->route('admin.products.edit', $product)->with('success', 'Товар обновлён');
    }

    // public function destroy(Product $product)
    // {
    //     // if ($product->image) {
        //     Storage::disk('public')->delete($product->image);
        // }
    //     $product->delete();
    //     return back()->with('success', 'Товар удалён');
    // }

    public function destroy(Product $product)
    {
        $product->delete();

        return response()->json(['status' => 'deleted']);
    }

    public function restore($slug)
    {
        $product = Product::withTrashed()
            ->where('slug', $slug)
            ->firstOrFail();
        $product->restore();
        return response()->json(['status' => 'restored']);
    }

//    public function deleteImage(ProductImage $image)
//    {
//        Storage::disk('public')->delete($image->path);
//        $image->delete();
//
//        return back()->with('success', 'Изображение удалено');
//    }

    public function forceDelete($slug)
    {
        $product = Product::withTrashed()
            ->where('slug', $slug)
            ->firstOrFail();

        // Удаляем главное изображение
        if ($product->image) {
            Storage::disk('public')->delete($product->image);
        }

        // Удаляем изображения галереи
        foreach ($product->images as $img) {
            Storage::disk('public')->delete($img->path);
        }

        // Удаляем товар окончательно
        $product->forceDelete();

        return response()->json(['status' => 'forceDeleted']);
    }

}
