<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Helper\ProductView;
use App\Models\Product;
use App\Models\Slide;
use App\Service\PriceService;

final class LinkController extends Controller
{
    private PriceService $pricing;

    public function __construct(PriceService $pricing)
    {
        $this->pricing = $pricing;
    }
    /**
     * Display the user's profile form.
     */
    public function home(?string $token = null)
    {

        $baseQuery = Product::query()
            ->with([
                'activePromotion',
                'categorie:id,nom',
            ])
            ->ByStock();

        $latestModels = (clone $baseQuery)->latest()->take(10)->get();
        $popularModels = (clone $baseQuery)->where('favoris', 1)->get();

        $latest = ProductView::collection($latestModels, $this->pricing);
        $popular = ProductView::collection($popularModels, $this->pricing);

        $slide = Slide::select('id', 'text_one', 'text_two', 'paragraph', 'image')->get();

        return view('index', compact('popular', 'latest', 'slide'));
    }

    public function product_detail(int $id, string $slug)
    {
        $baseQuery = Product::query()
            ->with([
                'activePromotion',
                'categorie:id,nom',
            ]);
        // Récupérer le produit par ID
        $product = (clone $baseQuery)->findOrFail($id);
        $product->loadMissing('images');
        $rows = ProductView::collection((clone $baseQuery)->where('categorie_id', $product->categorie_id)->ByStock()->take(4)->get(), $this->pricing);
        // Vérifier si le slug correspond
        if ($product->slug !== $slug) {
            return redirect()->route('shop.show', ['id' => $product->id, 'slug' => $product->slug]);
        }

        return view('product-show', compact('product', 'rows'));
    }
}
