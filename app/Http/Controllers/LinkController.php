<?php

namespace App\Http\Controllers;

use App\Models\Category;
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
        $query = Product::ByStock();

        // Récupération des derniers produits
        $latest = $query->take(4)->latest()->get();
        // Récupération des produits populaires
        $popular = $query->where('favoris', 1)->get();
        // add custom attributes
        $popular->append(['prix_promo', 'prix_format', 'reduction']);
        $latest->append(['prix_promo', 'prix_format', 'reduction']);

        $slide = Slide::all();

        return Inertia::render('Home', \compact('popular', 'latest', 'slide'));
    }

    public function langchange(string $lang)
    {
        session()->put('locale', $lang);

        return back();
    }

    public function shop(?Category $category = null)
    {
        $rows = Product::with('promotions')->ByStock()->when(Request::input('search'), function ($query, $search) {
            $query->whereAny(['nom', 'color'], 'LIKE', '%'.$search.'%');
        })->when($category, function ($query, $category) {
            $query->where('categorie_id', $category->id);
        })->latest('id')->paginate(15)->through(function ($row) {
            $row->prix_promo = $row->getPrixPromoAttribute();
            $row->prix_format = $row->getPrixFormatAttribute();
            $row->reduction = $row->getReductionAttribute();

            return $row;
        });
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
        $product->append(['prix_promo', 'prix_format', 'reduction']);
        $product->load('images');

        $rows = Product::where('categorie_id', $product->categorie_id)->ByStock()->take(4)->get();
        $rows->append(['prix_promo', 'prix_format', 'reduction']);
        $category = Category::all();

        return Inertia::render('Shop/Show', compact('product', 'rows', 'category'));
    }
}
