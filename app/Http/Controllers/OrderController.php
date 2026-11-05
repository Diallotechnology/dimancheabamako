<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enum\OrderEnum;
use App\Exceptions\StaleCartException;
use App\Helper\DeleteAction;
use App\Helper\OrderAPI;
use App\Http\Requests\StoreOrderRequest;
use App\Http\Requests\UpdateOrderRequest;
use App\Models\Order;
use App\Service\CartService;
use App\Service\OrderService;
use Carbon\Carbon;
use DomainException;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
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

                // Seule cette session pourra annuler la commande via order.cancel.
                session(['pending_order_ref' => $response['reference']]);

                return $response['_links']['payment']['href'];
            });

            return redirect()->away($redirectUrl);
        } catch (StaleCartException) {
            // Prix, promo ou stock modifiés depuis l'ajout au panier : le panier est déjà resynchronisé.
            session()->flash('warning', __('messages.panier.price_changed'));

            return back();
        } catch (DomainException $e) {
            // Tarif de livraison périmé ou incohérent (panier modifié depuis l'affichage) : cas légitime.
            session()->flash('warning', __('messages.panier.shipping_changed'));

            return back();
        } catch (Throwable $e) {
            // Le corps de la réponse N-Genius est loggé juste avant par OrderAPI::createOrder ; ici : cause + origine.
            Log::error('Order failed rollback', [
                'exception' => $e::class,
                'error' => $e->getMessage(),
                'at' => basename($e->getFile()).':'.$e->getLine(),
            ]);
            flash()->error('La validation a échoué. Aucune donnée enregistrée.');

            return back();
        }
    }

    /**
     * Ancien lien (référence N-Genius seule, sans signature) : réservé aux commandes
     * créées avant la date de bascule, pour ne pas casser les e-mails déjà envoyés.
     */
    public function invoice(string $id): View
    {
        return $this->renderInvoice(
            $id,
            Carbon::parse((string) config('invoice.legacy_cutoff')),
        );
    }

    /**
     * Lien signé (middleware `signed`) : la référence N-Genius seule ne suffit plus.
     */
    public function invoiceSigned(string $id): View
    {
        return $this->renderInvoice($id);
    }

    /**
     * Retour du client depuis N-Genius (redirectUrl). N-Genius redirige ici quel que soit le
     * résultat du paiement : on ne l'affiche comme réussi que si la passerelle confirme PURCHASED
     * pour la commande de cette session. Lecture seule : `app:check-payment` reste seul à modifier
     * l'état de la commande.
     */
    public function valid(Request $request): View
    {
        $reference = (string) $request->query('ref', '');
        $pending = (string) $request->session()->get('pending_order_ref', '');

        $status = 'pending';

        if ($reference !== '' && $pending !== '' && hash_equals($pending, $reference)) {
            try {
                $state = data_get($this->getOrderStatus($reference), '_embedded.payment.0.state');

                if ($state === 'PURCHASED') {
                    $status = 'success';
                } elseif (in_array($state, ['FAILED', 'CANCELLED', 'EXPIRED'], true)) {
                    $status = 'failed';
                }
            } catch (Throwable $e) {
                Log::warning('Order validate: payment status unavailable', ['trans_ref' => $reference]);
            }
        }

        if ($status === 'success') {
            $this->cart->clear();
        }

        if ($status !== 'pending') {
            $request->session()->forget('pending_order_ref');
        }

        return view('validate', ['status' => $status]);
    }

    /**
     * Retour du client depuis la page de paiement N-Genius (annulation).
     *
     * Ne supprime qu'une commande impayée, et seulement depuis la session qui l'a passée.
     * La commande n'est supprimée que si N-Genius confirme l'annulation du lien de paiement ;
     * sinon `app:check-payment` tranche (PURCHASED / CANCELLED / EXPIRED).
     */
    public function cancel(Request $request)
    {
        $reference = (string) $request->query('ref', '');
        $pending = (string) $request->session()->get('pending_order_ref', '');

        $cancelled = false;

        if ($reference !== '' && $pending !== '' && hash_equals($pending, $reference)) {
            try {
                $cancelled = DB::transaction(function () use ($reference): bool {
                    $order = Order::whereTransRef($reference)->lockForUpdate()->first();

                    if ($order === null || $order->reference !== null || $order->trans_state === 'PURCHASED') {
                        return false;
                    }

                    $this->cancelPaymentLink($reference);
                    $order->releaseStock();
                    $order->delete();

                    return true;
                });
            } catch (Throwable $e) {
                Log::warning('Order cancel refused by gateway, order kept', ['trans_ref' => $reference]);
            }
        }

        if ($cancelled) {
            $request->session()->forget('pending_order_ref');
            flash()->success('Commande annulée avec succès!');
        } else {
            flash()->warning("Aucune commande n'a été annulée.");
        }

        return redirect()->route('home');
    }

    /**
     * Display the specified resource.
     */
    public function show(Order $order)
    {
        $order->loadMissing('client', 'products', 'country')->loadSum('products as totaux', 'order_product.montant');

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

    private function renderInvoice(string $ref, ?Carbon $createdBefore = null): View
    {
        $order = Order::with('client', 'products')->withSum('products as totaux', 'order_product.montant')
            ->where('trans_ref', $ref)->where('trans_state', 'PURCHASED')
            ->when($createdBefore, fn ($query) => $query->where('created_at', '<', $createdBefore))
            ->firstOrFail();

        return view('invoice', compact('order'));
    }
}
