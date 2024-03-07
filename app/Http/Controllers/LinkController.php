<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Devise;
use App\Models\Product;
use App\Models\Slide;
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
        session()->put('locale', $lang);

        return redirect()->back();
    }

    public function livraison()
    {
        return Inertia::render('Livraison');
    }

    public function getTaux()
    {
        $taux = '';

        if (session('locale') === 'fr') {
            $taux = Devise::whereType('EUR')->first('taux');
        } elseif (session('locale') === 'en') {
            $taux = Devise::whereType('USD')->first('taux');
        }

        return $taux->taux;
    }

    public function shop(?Category $category = null)
    {

        $query =
            Product::when(Request::input('search'), function ($query, $search) {
                $query->whereAny(['nom', 'color'], 'LIKE', '%'.$search.'%');
            })->when($category, function ($query, $category) {
                $query->where('categorie_id', $category->id);
            });
        $rows = $query->latest('id')->paginate(15)->withQueryString();

        $filter = Request::only('search');
        $categorie = Category::all();
        $desc = $category ? $category->description : '';

        return Inertia::render('Shop/Index', compact('categorie', 'filter', 'rows', 'desc'));
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
