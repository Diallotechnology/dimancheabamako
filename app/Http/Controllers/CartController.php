<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Models\Product;
use App\Models\Shipping;
use App\Models\Transport;
use Darryldecode\Cart\Facades\CartFacade;
use Inertia\Inertia;

class CartController extends Controller
{
    private function get_userid()
    {
        // if (! auth()->check()) {
        //     if (session()->has('user_id')) {
        //         $userId = session()->get('user_id');
        //     }
        // } else {
        //     $userId = auth()->user()->id;
        // }
        if (! auth()->check()) {
            if (session()->has('user_id')) {
                $userId = session()->get('user_id');
            } else {
                $userId = \uniqid();
                session()->put('user_id', $userId);
            }
        } else {
            $userId = auth()->user()->id;
        }

        return $userId;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {

        $items = CartFacade::session($this->get_userid())->getContent()->sortBy('name');
        // get total qte
        $TotalQuantity = CartFacade::session($this->get_userid())->getTotalQuantity();
        // get total price
        $Total = CartFacade::session($this->get_userid())->getTotal();
        $pays = Country::all();
        $transport = Transport::all();

        return Inertia::render('Panier', compact('items', 'TotalQuantity', 'Total', 'pays', 'transport'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function GetCount()
    {
        return CartFacade::session($this->get_userid())->getContent()->count();
    }

    public function GetCountry()
    {
        return Country::all();
    }

    public function GetShipping($id, $trans_id)
    {
        return Shipping::whereCountryId($id)->whereTransportId($trans_id)->first();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Product $product)
    {

        $productSelected = $product;

        $productAdded = [
            'id' => $productSelected->id,
            'name' => $productSelected->nom,
            'price' => $productSelected->prix,
            'quantity' => 1,
            'attributes' => [],
            'associatedModel' => $productSelected,
        ];

        $cartNotEmpty = ! CartFacade::session($this->get_userid())->isEmpty();

        if ($cartNotEmpty && CartFacade::session($this->get_userid())->getContent()->containsStrict('id', $productSelected->id)) {
            return response()->json([
                'message' => 'Produit existe deja dans le panier!',
                'type' => false,
            ]);
        }

        CartFacade::session($this->get_userid())->add($productAdded);
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
    public function update(int $id, int $qte)
    {
        $user = '';
        if (! auth()->check()) {
            if (session()->has('user_id')) {
                $user = session()->get('user_id');
            }
        } else {
            $user = auth()->user()->id;
        }
        // $event = $this->emit('panier_updated');

        $cart = CartFacade::session($user)->get($id);

        // si qte inferieure a 1
        if ($qte < 1) {
            // qte  = 1
            CartFacade::session($user)->update($cart->id, [
                'quantity' => [
                    'relative' => false,
                    'value' => 1,
                ],
            ]);

            return response()->json([
                'message' => 'Quantité minimun est de 1!',
                'type' => false,
            ]);
        }

        // si qte superieure au stock
        if ($qte > $cart->associatedModel->stock) {

            return response()->json([
                'message' => 'Quantité non disponible!',
                'type' => false,
            ]);
        }

        // si qte inferieure au stock et si qte > = 1
        if ($qte <= $cart->associatedModel->stock and $cart->quantity >= 1) {
            // add qte demander
            CartFacade::session($user)->update($cart->id, [
                'quantity' => [
                    'relative' => false,
                    'value' => $qte,
                ],
            ]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $product = CartFacade::session($this->get_userid())->get($id);
        CartFacade::session($this->get_userid())->remove($product->id);

        return response()->json([
            'message' => 'Produit supprimer du panier avec success!',
            'type' => true,
        ]);
    }
}
