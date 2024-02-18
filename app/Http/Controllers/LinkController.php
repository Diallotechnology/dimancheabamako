<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Facades\Request;
use Inertia\Inertia;

class LinkController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function home()
    {
        $query = Product::query();
        $product = $query->take(4)->latest()->get();
        $latest = $query->where('favoris', 1)->get();

        return Inertia::render('Home', \compact('product', 'latest'));
    }

    public function livraison()
    {
        return Inertia::render('Livraison');
    }

    public function shop(Category $category)
    {
        $rows =
            Product::when(Request::input('search'), function ($query, $search) {
                $query->where('nom', 'like', '%'.$search.'%')->orwhere('color', 'like', '%'.$search.'%');
            })->when($category, function ($query, $category) {
                $query->where('categorie_id', $category->id);
            })->latest('id')->paginate(15)->withQueryString();
        $filter = Request::only('search', 'cat');
        $categorie = Category::all();

        return Inertia::render('Shop/Index', \compact('categorie', 'filter', 'rows'));
    }

    public function getCategory()
    {
        return Category::all(['id', 'nom']);
    }

    public function shopshow(Product $product)
    {
        $product->load('images');
        $rows = Product::where('categorie_id', $product->id)->take(4)->get();
        $category = Category::all();

        return Inertia::render('Shop/Show', compact('product', 'rows', 'category'));
    }
}
