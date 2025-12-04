<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enum\OrderEnum;
use App\Helper\CartAction;
use App\Helper\DeleteAction;
use App\Helper\OrderAPI;
use App\Http\Requests\StoreOrderRequest;
use App\Http\Requests\UpdateOrderRequest;
use App\Models\Order;
use App\Service\CartService;
use App\Service\OrderService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Throwable;

use function Flasher\Prime\flash;

final class OrderController extends Controller
{
    use DeleteAction, OrderAPI;


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
    private CartService $cart;

    public function __construct(CartService $cart)
    {
        $this->cart = $cart;
    }
    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreOrderRequest $request)
    {
        if ($this->cart->getContent()->isEmpty()) {
            flash()->error('Panier vide !');

            return back();
        }

        if (! $request->integer('livraison')) {
            flash()->error('Aucun transporteur disponible.');

            return back();
        }

        // 1. Création de la commande (transaction DB)
        try {
            $order = app(OrderService::class)->createOrder($request, $this->cart);
        } catch (Throwable $e) {
            flash()->error($e->getMessage());

            return back();
        }

        // 2. Création du lien de paiement (API externe → hors transaction)
        try {
            $montant = (int) $this->cart->getTotal() + $order->shipping;

            $postData = $this->prepareTransactionData(
                $montant,
                'XOF',
                $request->email,
                route('order.validate'),
                route('order.cancel')
            );

            $token = $this->getAccessToken();
            $response = $this->createOrder($postData, $token);

            if (! isset($response['_links']['payment']['href'])) {
                throw new Exception('Lien de paiement introuvable');
            }

            // mettre à jour la commande
            $order->update([
                'trans_ref' => $response['reference'],
            ]);

            return redirect()->away($response['_links']['payment']['href']);
        } catch (Throwable $e) {
            // rollback API sans rollback DB
            flash()->error('Le paiement n’a pas pu être initialisé.');

            return back();
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
        $this->cart->clear();

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

        $this->cart->clear();
        flash()->success('Commande annulée avec succès!');

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
