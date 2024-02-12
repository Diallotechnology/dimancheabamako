<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Darryldecode\Cart\Facades\CartFacade;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        \dd($request->all());
        $productId = 1;
        $userId = Auth::user()->id;
        $productSelected = Product::findOrFail($productId);

        $productAdded = [
            'id' => $productSelected->id,
            'name' => $productSelected->nom,
            'price' => $productSelected->prix_vente,
            'quantity' => $productSelected->qte_min,
            'attributes' => [],
            'associatedModel' => $productSelected,
        ];

        $cartNotEmpty = ! CartFacade::session($userId)->isEmpty();

        if ($cartNotEmpty && CartFacade::session($userId)->getContent()->containsStrict('id', $productId)) {
            return $this->alert('warning', 'Produit existe deja dans le panier!');
        }

        CartFacade::session($userId)->add($productAdded);
        if ($cartNotEmpty) {
            $this->alert('success', 'Produit ajouter au panier avec success!');
        }

        return $this->alert('success', 'Produit ajouter au panier avec success!');
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
