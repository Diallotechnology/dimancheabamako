<?php

namespace App\Http\Controllers;

use App\Enum\OrderEnum;
use App\Enum\RoleEnum;
use App\Helper\DeleteAction;
use App\Http\Requests\StoreOrderRequest;
use App\Http\Requests\UpdateOrderRequest;
use App\Models\Client;
use App\Models\Country;
use App\Models\Order;
use App\Models\User;
use Darryldecode\Cart\Facades\CartFacade;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class OrderController extends Controller
{
    use DeleteAction;

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreOrderRequest $request)
    {
        DB::transaction(function () use ($request) {

            $user = '';
            if (! auth()->check()) {
                if (session()->has('user_id')) {
                    $user = session()->get('user_id');
                }
            } else {
                $user = auth()->user()->id;
            }

            if (CartFacade::session($user)->getContent()->count() == 0) {
                return response()->json([
                    'message' => 'Panier vide!',
                    'type' => true,
                ]);
            }

            if ($request->password && ! empty($request->password)) {
                User::firstOrCreate(['email' => $request->email], [
                    'name' => $request->prenom,
                    'email' => $request->email,
                    'role' => RoleEnum::CUSTOMER->value,
                    'password' => Hash::make($request->password),
                ]);
            }

            $pays = Country::findOrFail($request->country_id);
            // register client
            $client = Client::firstOrCreate(['email' => $request->email], [
                'prenom' => $request->prenom,
                'nom' => $request->nom,
                'contact' => $request->contact,
                'pays' => $pays->nom,
            ]);

            // register client order infos
            $data = new Order([
                'payment' => $request->payment,
                'adresse' => $request->adresse,
                'postal' => $request->postal,
                'ville' => $request->ville,
                'country_id' => $pays->id,
                'transport_id' => $request->transport_id,
                'commentaire' => $pays->commentaire,
            ]);
            // save client order infos
            $order = $client->orders()->save($data);

            // Variable pour suivre si une erreur de stock est survenue
            $erreurStockInsuffisant = false;
            // get user cart content
            $panier = CartFacade::session($user)->getContent();
            // dd($panier);
            // add pivot table value
            $panier->each(function ($product) use ($order, $erreurStockInsuffisant) {
                if ($product->quantity > $product->associatedModel->stock) {
                    // Indique qu'une erreur s'est produite
                    $erreurStockInsuffisant = true;

                    // Arrête l'itération de la boucle
                    return response()->json([
                        'message' => "Quantité demandée d'un produit non disponible!!",
                        'type' => \false,
                    ]);
                } else {
                    $order->products()->attach($product->associatedModel->id, [
                        'quantity' => $product->quantity,
                        'montant' => $product->price * $product->quantity,
                    ]);

                    // Update product stock in DB
                    $stock = $product->associatedModel->stock - $product->quantity;
                    // Get product id and update quantity in DB
                    $product->associatedModel->update(['stock' => $stock]);
                }
            });

            // Si une erreur de stock est survenue, annule la transaction
            if ($erreurStockInsuffisant) {
                return back();
            }
            $order->save();
            $order->generateId('FA');
            // Supprime le contenu du panier utilisateur
            CartFacade::session($user)->clear();

            return response()->json([
                'message' => 'Commande effectuée avec success!',
                'type' => true,
            ]);
        });

    }

    /**
     * Display the specified resource.
     */
    public function show(Order $order)
    {
        $order->load('client', 'products')->loadSum('products as totaux', 'order_product.montant');

        $state = OrderEnum::cases();

        return Inertia::render('Admin/Order/Show', compact('order', 'state'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Order $order)
    {
        return Inertia::render('Admin/Order/Update', compact('order'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateOrderRequest $request, Order $order)
    {
        $order->update($request->validated());

        return back();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Order $order)
    {
        return $this->supp($order);
    }
}
