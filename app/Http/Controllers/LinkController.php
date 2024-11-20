<?php

namespace App\Http\Controllers;

use App\Helper\OrderAPI;
use App\Livewire\Produit;
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
        $query = Product::query()->ByStock();
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
        return Category::select('id', 'nom')->get();
    }

    public function product_detail(int $id, string $slug)
    {
        // Récupérer le produit par ID
        $product = Product::findOrFail($id);
        $product->loadMissing('images', 'categorie');
        $rows = Product::where('categorie_id', $product->categorie_id)->ByStock()->take(4)->get();
        $category = Category::select('id', 'nom')->get();
        // Vérifier si le slug correspond
        if ($product->slug !== $slug) {
            return redirect()->route('shop.show', ['id' => $product->id, 'slug' => $product->slug]);
        }

        return view('product-show', compact('product', 'rows', 'category'));
    }
}
