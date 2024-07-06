<?php

namespace App\Http\Controllers;

use App\Enum\OrderEnum;
use App\Enum\RoleEnum;
use App\Helper\DeleteAction;
use App\Helper\OrderAPI;
use App\Http\Requests\StoreOrderRequest;
use App\Http\Requests\UpdateOrderRequest;
use App\Mail\OrderMail;
use App\Models\Client;
use App\Models\Country;
use App\Models\Order;
use App\Models\Shipping;
use App\Models\User;
use Darryldecode\Cart\Facades\CartFacade;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;

class OrderController extends Controller
{
    use DeleteAction, OrderAPI;

    private function prepareTransactionData($montant, $currencyCode, $emailAddress, $redirectUrl, $cancelUrl)
    {
        return [
            'action' => 'PURCHASE',
            'language' => session('locale'),
            'emailAddress' => $emailAddress,
            'amount' => [
                'currencyCode' => $currencyCode,
                'value' => $montant,
            ],
            'merchantAttributes' => [
                'redirectUrl' => $redirectUrl,
                'cancelUrl' => $cancelUrl,
                'cancelText' => session('locale') === 'fr' ? 'Continuer mes achats' : 'Continue Shopping',
            ],
        ];
    }

    private function createOrder($accessToken, $postData)
    {
        $outlet = env('NGENIUS_OUTLET_ID');
        $response = Http::withToken($accessToken)
            ->withHeaders([
                'Content-Type' => 'application/vnd.ni-payment.v2+json',
                'Accept' => 'application/vnd.ni-payment.v2+json',
            ])
            ->post('https://api-gateway.sandbox.ngenius-payments.com/transactions/outlets/'.$outlet.'/orders', $postData);

        if ($response->successful()) {
            return $response->json();
        } else {
            Log::error('Failed to create order', ['response' => $response->json()]);

            return null;
        }
    }

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

            return response()->json(['message' => 'Panier vide!']);
        }

        DB::transaction(function () use ($request, $user, &$link, &$transactionSucceeded) {
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
                'poids' => $totalWeight.' Kg',
                'shipping' => $shipping->montant,
                'transport_id' => $request->transport_id,
                'commentaire' => $request->commentaire,
            ]);

            // save client order infos
            $order = $client->orders()->save($data);

            // Variable pour suivre si une erreur de stock est survenue
            $erreurStockInsuffisant = false;

            // add pivot table value
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

            // generate payment link
            $montant = intval($panier->getTotal()) + $shipping->montant;
            $currencyCode = 'XOF';
            $emailAddress = $request->email;
            $redirectUrl = route('order.validate');
            $cancelUrl = route('order.cancel');

            $accessToken = $this->getAccessToken();
            if ($accessToken) {
                $postData = $this->prepareTransactionData($montant, $currencyCode, $emailAddress, $redirectUrl, $cancelUrl);
                $response = $this->createOrder($accessToken, $postData);
                if ($response && isset($response['_links']['payment']['href'])) {
                    $link = $response['_links']['payment']['href'];
                    // save temporaly order
                    $order->update(['trans_ref' => $response['reference']]);
                    // Supprime le contenu du panier utilisateur
                    CartFacade::session($user)->clear();
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

    public function invoice(int $id)
    {
        $order = Order::with('client', 'products')->withSum('products as totaux', 'order_product.montant')->findOrFail($id);

        return view('invoice', compact(['order']));
    }

    public function valid(Request $request)
    {
        $orderReference = $request->query('ref');

        if (! $orderReference) {
            Log::error('No order reference provided');

            return view('validate');
        }

        try {
            $order = Order::whereTransRef($orderReference)->firstOrFail();
        } catch (\Exception $e) {
            Log::error('Order not found', ['reference' => $orderReference, 'error' => $e->getMessage()]);

            return view('validate');
        }

        $responseData = $this->getOrderStatut($order->trans_ref);

        if ($responseData) {
            DB::beginTransaction();

            try {
                if (isset($responseData['_embedded']['payment'][0]['state'])) {
                    $paymentState = $responseData['_embedded']['payment'][0]['state'];
                    if ($paymentState === 'PURCHASED') {
                        $order->updateOrFail(['trans_state' => $paymentState]);
                        if ($order->reference == null) {
                            $order->generateId();
                        }
                        Mail::to($order->client->email)->send(new OrderMail($order));
                    }
                } else {
                    Log::warning("The 'state' key was not found in the transaction", ['response' => $responseData]);
                }

                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Failed to update order or send mail', ['order' => $order->trans_ref, 'error' => $e->getMessage()]);

                return view('validate');
            }
        } else {
            Log::error('Failed to retrieve order status', ['reference' => $order->trans_ref]);
        }

        return view('validate');
    }

    public function cancel(Request $request)
    {
        $orderReference = $request->query('ref');
        if ($orderReference && ! empty($orderReference)) {
            $order = Order::whereTransRef($orderReference)->firstOrFail();
            $order->delete();
        }
        toastr()->success('Commande annuler avec success!');

        return redirect('/');
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
