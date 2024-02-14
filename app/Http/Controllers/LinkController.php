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
        $product = Product::take(4)->get();

        return Inertia::render('Home', \compact('product'));
    }

    public function livraison()
    {
        return Inertia::render('Livraison');
    }

    public function shop()
    {
        $rows =
            Product::when(Request::input('search'), function ($query, $search) {
                $query->where('nom', 'like', '%'.$search.'%')->orwhere('color', 'like', '%'.$search.'%');
            })->when(Request::input('cat'), function ($query, $cat) {
                $query->where('categorie_id', $cat);
            })->latest('id')->paginate(15)->withQueryString();
        $filter = Request::only('search', 'cat');
        $category = Category::all();

        return Inertia::render('Shop/Index', \compact('category', 'filter', 'rows'));
    }

    public function shopshow(Product $product)
    {
        $product->load('images');
        $rows = Product::where('categorie_id', $product->id)->take(4)->get();
        $category = Category::all();

        return Inertia::render('Shop/Show', compact('product', 'rows', 'category'));
    }
}
