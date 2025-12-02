<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Helper\ProductView;
use App\Models\Product;
use App\Models\Slide;
use App\Service\PriceService;

final class LinkController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function home(?string $token = null)
    {
        $pricing = app(PriceService::class);

        $baseQuery = Product::query()
            ->with([
                'promotions' => fn ($q) => $q->active()->orderByDesc('id'),
                'categorie:id,nom',
            ])
            ->active()
            ->ByStock();

        $latestModels = (clone $baseQuery)->latest()->take(10)->get();
        $popularModels = (clone $baseQuery)->where('favoris', 1)->get();

        $latest = ProductView::collection($latestModels, $pricing);
        $popular = ProductView::collection($popularModels, $pricing);

        $slide = Slide::select('id', 'text_one', 'text_two', 'paragraph', 'image')->get();

        return view('index', compact('popular', 'latest', 'slide'));
    }

    public function product_detail(int $id, string $slug)
    {
        // Récupérer le produit par ID
        $product = Product::findOrFail($id);
        $product->loadMissing('images', 'categorie');
        $rows = Product::where('categorie_id', $product->categorie_id)->ByStock()->take(4)->get();
        // Vérifier si le slug correspond
        if ($product->slug !== $slug) {
            return redirect()->route('shop.show', ['id' => $product->id, 'slug' => $product->slug]);
        }

        return view('product-show', compact('product', 'rows'));
    }
}
