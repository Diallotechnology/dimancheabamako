<?php

namespace App\Http\Controllers;

use App\Helper\OrderAPI;
use App\Models\Category;
use App\Models\Product;
use App\Models\Slide;

class LinkController extends Controller
{
    use OrderAPI;

    /**
     * Display the user's profile form.
     */
    public function home(?string $token = null)
    {
        $query = Product::ByStock();
        // Récupération des derniers produits
        $latest = $query->take(10)->latest()->get();
        // Récupération des produits populaires
        $popular = $query->where('favoris', 1)->get();
        // add custom attributes
        $popular->append(['prix_promo', 'prix_format', 'reduction']);
        $latest->append(['prix_promo', 'prix_format', 'reduction']);
        $slide = Slide::all();

        return view('index', compact('popular', 'latest', 'slide'));
    }

    public function getCategory()
    {
        return Category::all(['id', 'nom']);
    }

    public function shopshow(Product $product)
    {
        $product->load('images');
        $rows = Product::where('categorie_id', $product->categorie_id)->ByStock()->take(4)->get();
        $category = Category::all();

        return view('product-show', compact('product', 'rows', 'category'));
    }
}
