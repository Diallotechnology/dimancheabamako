<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Models\Devise;
use App\Models\Product;
use App\Models\Shipping;
use Darryldecode\Cart\Facades\CartFacade;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;
use Inertia\Response;

class CartController extends Controller
{
    private function get_userid(): string
    {
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
    public function index(): Response
    {

        $items = CartFacade::session($this->get_userid())->getContent()->sortBy('name');
        // get total qte
        $TotalQuantity = CartFacade::session($this->get_userid())->getTotalQuantity();
        // get total price
        $tauxConversion = session('locale') === 'fr' ? Devise::whereType('EUR')->value('taux') : Devise::whereType('USD')->value('taux');
        $Total = floatval(number_format(CartFacade::session($this->get_userid())->getTotal() / $tauxConversion, 2));
        $totalWeight = $items->pluck('attributes')->sum('poids');
        $country = Country::all('nom', 'id');

        return Inertia::render('Panier', compact('items', 'TotalQuantity', 'Total', 'country', 'totalWeight'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function GetDevise(): int
    {
        if (session('locale') === 'fr') {
            $taux = Devise::whereType('EUR')->value('taux');
        } elseif (session('locale') === 'en') {
            $taux = Devise::whereType('USD')->value('taux');
        }

        return $taux;
    }

    public function GetCount(): int
    {
        return CartFacade::session($this->get_userid())->getContent()->count();
    }

    public function GetTrans(int $country_id)
    {
        // get country
        $country = Country::with('zone')->find($country_id);
        if ($country->zone->transports->isNotEmpty()) {
            return $country->zone->transports;
        } else {
            return response()->json([
                'message' => 'Nous ne livrons pas dans ce pays!',
                'type' => true,
            ]);
        }

    }

    public function GetShipping(int $country, int $trans_id)
    {
        // Retrieve the total weight of the products in the cart
        $items = CartFacade::session($this->get_userid())->getContent()->sortBy('name');
        $totalWeight = $items->pluck('attributes')->sum('poids');
        // get zone id
        $pays = Country::findOrFail($country);
        // dd($totalWeight);
        try {
            // Fetch the shipping rule based on the country ID, transport ID, and weight range
            $shipping = Shipping::whereZoneId($pays->zone_id)
                ->whereTransportId($trans_id)
                ->whereRelation('poids', function ($query) use ($totalWeight) {
                    $query->where('min', '<=', $totalWeight)
                        ->where('max', '>=', $totalWeight);
                })->firstOrFail();

        } catch (ModelNotFoundException $e) {
            // Handle case where shipping rule is not found
            return response()->json([
                'message' => 'Aucune correspondance trouvé!',
                'type' => true,
            ]);
        }

        return $shipping;
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Product $product): JsonResponse
    {
        $product->append(['prix_final']);
        $productSelected = $product;
        $productAdded = [
            'id' => $productSelected->id,
            'name' => $productSelected->nom,
            'price' => $productSelected->getPrixFinal(),
            'quantity' => 1,
            'attributes' => ['poids' => $productSelected->poids],
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
    public function destroy(string $id): JsonResponse
    {
        $product = CartFacade::session($this->get_userid())->get($id);
        CartFacade::session($this->get_userid())->remove($product->id);

        return response()->json([
            'message' => 'Produit supprimer du panier avec success!',
            'type' => true,
        ]);
    }
}
