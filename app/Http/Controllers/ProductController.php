<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\Request;

class ProductController extends Controller
{

    // public function show(ProductVariant $variant)
    // {
    //     $variant->load(['images', 'labels', 'propertyOptions.property', 'product.brand']);

    //     $product = $variant->product;

    //     $relatedVariants = ProductVariant::where('id', '!=', $variant->id)
    //         ->whereHas('product', fn($q) => $q->where('category_id', $product->category_id))
    //         ->with(['images', 'product'])
    //         ->take(6)
    //         ->get();

    //     return view('products.show', compact('variant', 'product', 'relatedVariants'));
    // }

}
