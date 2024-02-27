<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\Slide;
use Illuminate\Support\Facades\App;
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
        $latest = $query->take(4)->latest()->get();
        $popular = $query->where('favoris', 1)->get();
        $slide = Slide::all();

        return Inertia::render('Home', \compact('popular', 'latest', 'slide'));
    }

    public function langchange(string $lang)
    {
        App::setLocale($lang);
        session()->put('locale', $lang);

        return redirect()->back();
    }

    public function livraison()
    {
        return Inertia::render('Livraison');
    }

    public function shop(?Category $category = null)
    {

        $query =
            Product::when(Request::input('search'), function ($query, $search) {
                $query->where('nom', 'like', '%'.$search.'%')->orwhere('color', 'like', '%'.$search.'%');
            })->when($category, function ($query, $category) {
                $query->where('categorie_id', $category->id);
            });
        $rows = $query->latest('id')->paginate(15)->withQueryString();
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
        $rows = Product::where('categorie_id', $product->categorie_id)->take(4)->get();
        $category = Category::all();

        return Inertia::render('Shop/Show', compact('product', 'rows', 'category'));
    }
}
