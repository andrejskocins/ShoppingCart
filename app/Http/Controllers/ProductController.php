<?php

namespace App\Http\Controllers;

use App\Models\Product;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->paginate(16);

        return view('products.index', [
            'products' => $products,
        ]);
    }
}
