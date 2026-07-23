<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function show(Category $category)
    {
        $relatedCategories = Category::where('parent_id', $category->parent_id)
            ->where('id', '!=', $category->id)
            ->take(3)
            ->get();

        return view('categories.show', compact('category', 'relatedCategories'));
    }

    public function catalog()
    {

        $categories = Category::query()->paginate(4)->withQueryString();

        return view('categories.index', compact('categories'));
    }

}
