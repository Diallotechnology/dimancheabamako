<?php

namespace App\Http\Controllers;

use App\Models\User;
use Inertia\Inertia;
use App\Models\Order;
use App\Enum\RoleEnum;
use App\Models\Client;
use App\Enum\OrderEnum;
use App\Models\Country;
use App\Helper\OrderAPI;
use App\Models\Shipping;
use App\Helper\CartAction;
use App\Helper\DeleteAction;
use Illuminate\Http\Request;
use App\Jobs\RegisterMailJob;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\StoreOrderRequest;
use App\Http\Requests\UpdateOrderRequest;
use Darryldecode\Cart\Facades\CartFacade;

class OrderController extends Controller
{
    use CartAction, DeleteAction, OrderAPI;


    // public function test()
    // {
    //     Order::whereNotNull('trans_ref')
    //         ->whereNull('reference')
    //         ->whereNull('trans_state')
    //         ->chunk(100, function ($orders) {
    //             foreach ($orders as $order) {
    //                 DB::transaction(function () use ($order) {
    //                     // Verrouiller la commande pour empêcher les accès concurrents
    //                     $order = Order::where('id', $order->id)->lockForUpdate()->first();

    //                     // Récupérer l'état de la commande via l'API
    //                     $responseData = $this->getOrderStatut($order->trans_ref);
    //                     if ($responseData) {
    //                         try {
    //                             // Parse `createDateTime` depuis les données reçues
    //                             $createDateTime = Carbon::parse($responseData['createDateTime']);
    //                             $currentTime = Carbon::now();

    //                             // Calculer la différence en minutes
    //                             $minutesDifference = $currentTime->diffInMinutes($createDateTime);

    //                             // Vérifier l'état du paiement
    //                             $paymentState = $responseData['_embedded']['payment'][0]['state'] ?? null;

    //                             if ($minutesDifference >= 5 && $paymentState !== 'PURCHASED') {
    //                                 dd($order);
    //                                 // Annuler le lien de paiement et supprimer la commande
    //                                 $this->cancelPaymentLink($order->trans_ref);
    //                                 $order->delete();

    //                                 Log::info('Order cancelled due to timeout', [
    //                                     'order_id' => $order->id,
    //                                     'trans_ref' => $order->trans_ref,
    //                                     'minutes_elapsed' => $minutesDifference,
    //                                 ]);
    //                             }
    //                         } catch (\Exception $e) {
    //                             Log::error('Failed to process order', [
    //                                 'order_id' => $order->id,
    //                                 'trans_ref' => $order->trans_ref,
    //                                 'error' => $e->getMessage(),
    //                             ]);
    //                             throw $e; // Relancer pour annuler la transaction
    //                         }
    //                     } else {
    //                         Log::error('Failed to retrieve order status', ['trans_ref' => $order->trans_ref]);
    //                     }
    //                 });
    //             }
    //         });
    // }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreOrderRequest $request)
    {
        $link = '';
        $transactionSucceeded = false;
        $user = auth()->check() ? auth()->user()->id : session('user_id', '');

        if (CartFacade::session($user)->getContent()->count() == 0) {
            toastr()->error('Panier vide!');

            return back();
        }
        if (! $request->livraison) {
            toastr()->error("Il n'y a pas de transporteur disponible pour livrer ce colis.");

            return back();
        }

        DB::transaction(function () use ($request, $user, &$link, &$transactionSucceeded) {
            if ($request->password && ! empty($request->password)) {
                $new_user = User::firstOrCreate(['email' => $request->email], [
                    'name' => $request->prenom,
                    'email' => $request->email,
                    'role' => RoleEnum::CUSTOMER->value,
                    'change_password' => true,
                    'password' => Hash::make($request->password),
                ]);
                // send register mail
                RegisterMailJob::dispatch($new_user);
            }

            $pays = Country::findOrFail($request->country_id);
            // register client
            $client = Client::firstOrCreate(['email' => $request->email], [
                'prenom' => $request->prenom,
                'nom' => $request->nom,
                'contact' => $request->contact,
                'pays' => $pays->nom,
            ]);

            // get user cart content
            $panier = CartFacade::session($user);
            // get product poids sum
            $totalWeight = $panier->getContent()->pluck('attributes')->sum('poids');
            $shipping = Shipping::findOrFail($request->livraison);

            // register client order infos
            $data = new Order([
                'adresse' => $request->adresse,
                'postal' => $request->postal,
                'ville' => $request->ville,
                'country_id' => $pays->id,
                'poids' => $totalWeight . ' Kg',
                'shipping' => $shipping->montant,
                'transport_id' => $request->transport_id,
                'commentaire' => $request->commentaire,
            ]);

            // save client order infos
            $order = $client->orders()->save($data);

            // Variable pour suivre si une erreur de stock est survenue
            $erreurStockInsuffisant = false;

            // // add pivot table value without updating stock
            $panier->getContent()->each(function ($product) use ($order, &$erreurStockInsuffisant) {
                if ($product->quantity > $product->associatedModel->stock) {
                    // Indique qu'une erreur s'est produite
                    $erreurStockInsuffisant = true;

                    return false; // Arrête l'itération de la boucle
                } else {
                    $order->products()->attach($product->associatedModel->id, [
                        'quantity' => $product->quantity,
                        'montant' => $product->price * $product->quantity,
                    ]);
                }
            });

            // Si une erreur de stock est survenue, annule la transaction
            if ($erreurStockInsuffisant) {
                toastr()->error("La quantité d'un produit est non disponible.");

                return back();
            }

            // Générer le lien de paiement
            $montant = intval($panier->getTotal()) + $shipping->montant;
            $currencyCode = 'XOF';
            $emailAddress = $request->email;
            $redirectUrl = route('order.validate');
            $cancelUrl = route('order.cancel');

            $accessToken = $this->getAccessToken();
            if ($accessToken) {
                $postData = $this->prepareTransactionData(
                    $montant,
                    $currencyCode,
                    $emailAddress,
                    $redirectUrl,
                    $cancelUrl
                );

                $response = $this->createOrder($postData, $accessToken);

                if ($response && isset($response['_links']['payment']['href'])) {
                    $link = $response['_links']['payment']['href'];
                    $order->update(['trans_ref' => $response['reference']]);
                    $transactionSucceeded = true;
                }
            }
        });

        if ($transactionSucceeded && $link) {
            return redirect()->away($link);
        } else {
            return abort(500, 'Unable to process payment');
        }
    }

    public function invoice(string $id)
    {
        $order = Order::with('client', 'products')->withSum('products as totaux', 'order_product.montant')
            ->where('trans_ref', $id)->where('trans_state', 'PURCHASED')
            ->firstOrFail();

        return view('invoice', compact('order'));
    }

    public function valid()
    {
        request()->fullUrlWithQuery(['ref' => null]);
        $this->cart_clear();

        return view('validate');
    }

    public function cancel(Request $request)
    {
        $orderReference = $request->query('ref');
        if ($orderReference && ! empty($orderReference)) {
            $order = Order::whereTransRef($orderReference)->firstOrFail();
            $order->delete();
            $this->cancelPaymentLink($order->trans_ref);
        }

        $this->cart_clear();
        toastr()->success('Commande annulée avec succès!');

        // Redirect to the home page or a URL without the 'ref' parameter
        return redirect()->route('home');
    }

    /**
     * Display the specified resource.
     */
    public function show(Order $order)
    {
        $order->loadMissing('client', 'products')->loadSum('products as totaux', 'order_product.montant');

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
