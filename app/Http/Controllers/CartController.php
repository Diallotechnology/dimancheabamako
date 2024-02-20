<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Models\Product;
use Darryldecode\Cart\Facades\CartFacade;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CartController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $items = CartFacade::session(1)->getContent()->sortBy('name');
        // get total qte
        $TotalQuantity = CartFacade::session(1)->getTotalQuantity();
        // get total price
        $Total = CartFacade::session(1)->getTotal();
        $pays = Country::all();

        return Inertia::render('Panier', compact('items', 'TotalQuantity', 'Total', 'pays'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function GetCount()
    {
        return CartFacade::session(1)->getContent()->count();
    }

    public function GetCountry()
    {
        return Country::all();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Product $product)
    {
        $productSelected = $product;
        $userId = 1;

        $productAdded = [
            'id' => $productSelected->id,
            'name' => $productSelected->nom,
            'price' => $productSelected->prix,
            'quantity' => 1,
            'attributes' => [],
            'associatedModel' => $productSelected,
        ];

        $cartNotEmpty = ! CartFacade::session($userId)->isEmpty();

        if ($cartNotEmpty && CartFacade::session($userId)->getContent()->containsStrict('id', $productSelected->id)) {
            return response()->json([
                'message' => 'Produit existe deja dans le panier!',
                'type' => false,
            ]);
        }

        CartFacade::session($userId)->add($productAdded);
        if ($cartNotEmpty) {
            return response()->json([
                'message' => 'Produit ajouter au panier avec success!',
                'type' => true,
            ]);
        }

        return response()->json([
            'message' => 'Produit ajouter au panier avec success!',
            'type' => true,
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
