<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;

final class SitemapController extends Controller
{
    // Sitemap index
    public function index()
    {
        $product = Product::latest()->first();

        return response()->view('sitemap.index', compact('product'))->header('Content-Type', 'text/xml');
    }

    // Sitemap page
    public function page()
    {
        return response()->view('sitemap.page')->header('Content-Type', 'text/xml');
    }

    // Sitemap product
    public function product()
    {
        $product = Product::latest()->get();

        return response()->view('sitemap.product', compact('product'))->header('Content-Type', 'text/xml');
    }

    // Sitemap category
    public function category()
    {
        $category = Category::latest()->get();

        return response()->view('sitemap.category', compact('category'))->header('Content-Type', 'text/xml');
    }
}
