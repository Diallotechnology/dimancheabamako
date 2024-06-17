<?php

namespace App\Http\Controllers;

use App\Enum\OrderEnum;
use App\Enum\RoleEnum;
use App\Helper\DeleteAction;
use App\Http\Requests\StoreOrderRequest;
use App\Http\Requests\UpdateOrderRequest;
use App\Mail\OrderMail;
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
use Illuminate\Support\Facades\Mail;
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
                'cancelText' => session('locale') === 'fr' ? 'Continuer mes achats' : 'Continue Shopping',
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
        // $test = Order::find(1);
        // $test2 = Mail::to('test@gmail.com')->send(new OrderMail($test));
        // dd($test2);
        $montant = 100;
        $currencyCode = 'XOF';
        $emailAddress = 'customer@test.com';
        $redirectUrl = 'https://mysite.com/redirect';
        $cancelUrl = route('home');

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

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreOrderRequest $request)
    {
        $link = '';
        $transactionSucceeded = false;
        $user = auth()->check() ? auth()->user()->id : session('user_id', '');

        if (CartFacade::session($user)->getContent()->count() == 0) {
            return response()->json([
                'message' => 'Panier vide!',
                'type' => true,
            ]);
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
                'trans_ref' => uniqid(),
                'reference' => uniqid(),
                'payment' => $request->payment,
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
            $cancelUrl = route('home');

            $apikey = 'ZGU3NmY1YTgtYWZmYy00NWNjLWI1ZGItYTI1NzQzMzMwMDBhOmJjMjQwYjllLTY5YmEtNDVlYy1hZWZhLTU4YTliNTQ3OTdjZQ==';
            $realmName = 'OBMaliSandbox';
            $outlet = 'af106601-8e01-41b5-bbb9-6b7fc82b71e5';

            $accessToken = $this->getAccessToken($apikey, $realmName);
            if ($accessToken) {
                $postData = $this->prepareTransactionData($montant, $currencyCode, $emailAddress, $redirectUrl, $cancelUrl);
                $response = $this->createOrder($outlet, $accessToken, $postData);
                if ($response && isset($response['_links']['payment']['href'])) {
                    $link = $response['_links']['payment']['href'];
                    // save temporaly order
                    $order->trans_ref = $response['reference'];
                    $order->save();
                    // Supprime le contenu du panier utilisateur
                    CartFacade::session($user)->clear();
                    $transactionSucceeded = true;
                }
            }
        });

        if ($transactionSucceeded && $link) {
            return redirect()->away($link);
        } else {
            return response()->json(['error' => 'Unable to process payment'], 500);
        }
    }

    public function invoice(int $id)
    {
        $order = Order::with('client', 'products')->withSum('products as totaux', 'order_product.montant')->findOrFail($id);

        return view('invoice', compact(['order']));
    }

    public function valid(string $ref)
    {
        dd($ref);

        return view('Validate', compact('order'));
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
