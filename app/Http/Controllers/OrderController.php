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
use App\Models\Shipping;
use App\Models\User;
use Darryldecode\Cart\Facades\CartFacade;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class OrderController extends Controller
{
    use DeleteAction;

    private function getOrderStatut($outlet, $reference, $apikey)
    {
        $response = Http::withHeaders([
            'accept' => 'application/vnd.ni-identity.v1+json',
            'authorization' => 'Basic '.$apikey,
            'content-type' => 'application/vnd.ni-identity.v1+json',
        ])->get('https://api-gateway.sandbox.ngenius-payments.com/transactions/outlets/'.$outlet.'/orders/'.$reference.'');

        if ($response->successful()) {
            return $response->json();
        } else {
            Log::error('Failed to get access token', ['response' => $response->json()]);

            return null;
        }
    }

    private function getAccessToken($apikey, $realmName)
    {
        $response = Http::withHeaders([
            'accept' => 'application/vnd.ni-identity.v1+json',
            'authorization' => 'Basic '.$apikey,
            'content-type' => 'application/vnd.ni-identity.v1+json',
        ])->post('https://api-gateway.sandbox.ngenius-payments.com/identity/auth/access-token', [
            'realmName' => $realmName,
        ]);

        if ($response->successful()) {
            return $response->json()['access_token'];
        } else {
            Log::error('Failed to get access token', ['response' => $response->json()]);

            return null;
        }
    }

    private function prepareTransactionData($montant, $currencyCode, $emailAddress, $redirectUrl, $cancelUrl)
    {
        return [
            'action' => 'PURCHASE',
            'emailAddress' => $emailAddress,
            'amount' => [
                'currencyCode' => $currencyCode,
                'value' => $montant,
            ],
            'merchantAttributes' => [
                'redirectUrl' => $redirectUrl,
                'cancelUrl' => $cancelUrl,
                'cancelText' => 'Default Continue Shopping',
            ],
        ];
    }

    private function createOrder($outlet, $accessToken, $postData)
    {
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

    public function processPayment()
    {
        $montant = 100;
        $currencyCode = 'XOF';
        $emailAddress = 'customer@test.com';
        $redirectUrl = 'https://mysite.com/redirect';
        $cancelUrl = 'https://myshop.com/basket';

        $apikey = 'ZGU3NmY1YTgtYWZmYy00NWNjLWI1ZGItYTI1NzQzMzMwMDBhOmJjMjQwYjllLTY5YmEtNDVlYy1hZWZhLTU4YTliNTQ3OTdjZQ==';
        $realmName = 'OBMaliSandbox';
        $outlet = 'af106601-8e01-41b5-bbb9-6b7fc82b71e5';

        $accessToken = $this->getAccessToken($apikey, $realmName);

        if ($accessToken) {
            $postData = $this->prepareTransactionData($montant, $currencyCode, $emailAddress, $redirectUrl, $cancelUrl);
            $response = $this->createOrder($outlet, $accessToken, $postData);

            if ($response && isset($response['_links']['payment']['href'])) {
                return redirect($response['_links']['payment']['href']);
            }
        }

        // Gérer les cas d'erreur de manière appropriée
        return response()->json(['error' => 'Unable to process payment'], 500);
    }

    public function FunctionName()
    {
        // Définir le montant et la nature en fonction du type de paiement
        $montant = 100;
        $type = 'gold';
        $nature = 'STANDART GOlD';

        // Entrez votre clé API ici
        $apikey = 'ZGU3NmY1YTgtYWZmYy00NWNjLWI1ZGItYTI1NzQzMzMwMDBhOmJjMjQwYjllLTY5YmEtNDVlYy1hZWZhLTU4YTliNTQ3OTdjZQ==';
        $ch = curl_init();
        // prod api access token link
        // https://api-gateway.orabankml.ngenius-payments.com/identity/auth/access-token
        // Obtenir un token d'accès depuis l'API

        curl_setopt($ch, CURLOPT_URL, 'https://api-gateway.sandbox.ngenius-payments.com/identity/auth/access-token');
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'accept: application/vnd.ni-identity.v1+json',
            'authorization: Basic '.$apikey,
            'content-type: application/vnd.ni-identity.v1+json',
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, '{"realmName":"OBMaliSandbox"}');

        $output = json_decode(curl_exec($ch));
        $access_token = $output->access_token;
        // dd($access_token);

        // Préparer les données de la transaction
        $postData = [
            'action' => 'PURCHASE',
            'emailAddress' => 'customer@test.com',
            'amount' => [
                'currencyCode' => 'XOF',
                'value' => $montant,
            ],
            'merchantAttributes' => [
                'redirectUrl' => 'https://mysite.com/redirect',
                'cancelUrl' => 'https://myshop.com/basket',
                'cancelText' => 'Default Continue Shopping',
            ],
        ];

        $outlet = 'af106601-8e01-41b5-bbb9-6b7fc82b71e5';
        $token = $access_token;
        $json = json_encode($postData);

        $ch2 = curl_init();

        // prod create order link
        // https://api-gateway.orabankml.ngenius-payments.com/transactions/outlets/'.$outlet.'/orders
        curl_setopt($ch2, CURLOPT_URL, 'https://api-gateway.sandbox.ngenius-payments.com/transactions/outlets/'.$outlet.'/orders');
        curl_setopt($ch2, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer '.$token,
            'Content-Type: application/vnd.ni-payment.v2+json',
            'Accept: application/vnd.ni-payment.v2+json',
        ]);
        curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch2, CURLOPT_POST, 1);
        curl_setopt($ch2, CURLOPT_POSTFIELDS, $json);

        $output2 = json_decode(curl_exec($ch2));
        // Extraire l'URL de paiement et la référence de la commande
        $paymentHref = $output2->_links->payment->href;
        $orderReference = $output2->_embedded->payment[0]->orderReference;

        return redirect($paymentHref);
        // echo $orderReference.' <br>';
        // echo $paymentHref.' <br>';
    }

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
            // get user cart content
            $panier = CartFacade::session($user);
            // get product poids sum
            $totalWeight = $panier->getContent()->pluck('attributes')->sum('poids');
            $shipping = Shipping::findOrFail($request->livraison);
            // register client order infos
            $data = new Order([
                'payment' => $request->payment,
                'adresse' => $request->adresse,
                'postal' => $request->postal,
                'ville' => $request->ville,
                'country_id' => $pays->id,
                'poids' => $totalWeight.' Kg',
                'shipping' => $shipping->montant,
                'transport_id' => $request->transport_id,
                'commentaire' => $pays->commentaire,
            ]);
            // save client order infos
            $order = $client->orders()->save($data);

            // Variable pour suivre si une erreur de stock est survenue
            $erreurStockInsuffisant = false;

            // dd($panier);
            // add pivot table value
            $panier->getContent()->each(function ($product) use ($order, $erreurStockInsuffisant) {
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

            // generate payement link
            $montant = $shipping->montant + $panier->getTotal();
            $currencyCode = 'XOF';
            $emailAddress = $request->email;
            $redirectUrl = route('order.validate');
            $cancelUrl = route('home');

            $apikey = 'ZGU3NmY1YTgtYWZmYy00NWNjLWI1ZGItYTI1NzQzMzMwMDBhOmJjMjQwYjllLTY5YmEtNDVlYy1hZWZhLTU4YTliNTQ3OTdjZQ==';
            $realmName = 'OBMaliSandbox';
            $outlet = 'af106601-8e01-41b5-bbb9-6b7fc82b71e5';

            $accessToken = $this->getAccessToken($apikey, $realmName);

            if ($accessToken) {
                $postData = $this->prepareTransactionData($montant, $currencyCode, $emailAddress, $redirectUrl, $cancelUrl);
                $response = $this->createOrder($outlet, $accessToken, $postData);

                if ($response && isset($response['_links']['payment']['href'])) {
                    // save temporaly order
                    $order->trans_ref = $response['reference'];
                    $order->save();
                    // Supprime le contenu du panier utilisateur
                    CartFacade::session($user)->clear();

                    return redirect($response['_links']['payment']['href']);
                }
            }

            // Gérer les cas d'erreur de manière appropriée
            return response()->json(['error' => 'Unable to process payment'], 500);
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
