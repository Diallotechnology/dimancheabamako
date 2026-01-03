<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enum\OrderEnum;
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
            session()->flash('warning', __('messages.panier.not_transport'));
            return back();
        }

        try {
            $redirectUrl = DB::transaction(function () use ($request) {

                $order = app(OrderService::class)->createOrder($request, $this->cart);

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

                $order->update(['trans_ref' => $response['reference']]);

                return $response['_links']['payment']['href'];
            });

            return redirect()->away($redirectUrl);
        } catch (Throwable $e) {
            Log::error('Order failed rollback', ['error' => $e->getMessage()]);
            flash()->error('La validation a échoué. Aucune donnée enregistrée.');
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

        $this->cart->clear();
        \redirect()->to(request()->fullUrlWithQuery(['ref' => null]));

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
        flash()->success('Commande mise à jour avec succès!');
        return back();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Order $order)
    {
        $order->delete();
        flash()->success('Commande supprimer avec succès!');
    }
}
