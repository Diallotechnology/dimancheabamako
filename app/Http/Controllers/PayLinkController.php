<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Helper\DeleteAction;
use App\Helper\OrderAPI;
use App\Http\Requests\StorePayLinkRequest;
use App\Models\PayLink;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Throwable;

use function Flasher\Prime\flash;

final class PayLinkController extends Controller
{
    use DeleteAction, OrderAPI;

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePayLinkRequest $request)
    {
        DB::beginTransaction();

        try {
            $montant = $request->integer('montant');
            $currencyCode = 'XOF';
            $emailAddress = '';
            $redirectUrl = route('home');
            $cancelUrl = route('home');

            // 1) Récupération Token API
            $accessToken = $this->getAccessToken();

            // 2) Préparation payload API
            $postData = $this->prepareTransactionData(
                $montant,
                $currencyCode,
                $emailAddress,
                $redirectUrl,
                $cancelUrl,
                'fr'
            );

            // 3) Appel API → création order
            $response = $this->createOrder($postData, $accessToken);

            if (! isset($response['_links']['payment']['href'])) {
                throw new Exception('Réponse API invalide : lien de paiement manquant');
            }

            // 4) Sauvegarde DB atomique
            $data = $request->validated();
            $data['lien'] = $response['_links']['payment']['href'];
            $data['trans_ref'] = $response['reference'];
            $data['etat'] = 'Pending';

            PayLink::create($data);

            DB::commit();

            flash()->success('Lien de paiement généré avec succès.');

            return back();
        } catch (Throwable $e) {

            DB::rollBack();

            Log::error('Erreur PayLink::store()', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            flash()->error('Impossible de créer le lien de paiement.');

            return back();
        }
    }

    public function regenerate(PayLink $paylink)
    {
        DB::beginTransaction();

        try {
            $currencyCode = 'XOF';
            $emailAddress = '';
            $redirectUrl = route('home');
            $cancelUrl = route('home');

            // Token API
            $accessToken = $this->getAccessToken();

            // Payload
            $postData = $this->prepareTransactionData(
                $paylink->montant,
                $currencyCode,
                $emailAddress,
                $redirectUrl,
                $cancelUrl
            );

            // Appel API
            $response = $this->createOrder($postData, $accessToken);

            if (! isset($response['_links']['payment']['href'])) {
                throw new Exception('Réponse API invalide : lien de paiement absent');
            }

            // Mise à jour atomique
            $paylink->update([
                'lien' => $response['_links']['payment']['href'],
                'trans_ref' => $response['reference'],
                'etat' => 'Pending',
            ]);

            DB::commit();

            flash()->success('Le lien de paiement a été régénéré avec succès.');

            return back();
        } catch (Throwable $e) {

            DB::rollBack();

            Log::error('Erreur PayLink::regenerate()', [
                'paylink_id' => $paylink->id,
                'message' => $e->getMessage(),
            ]);

            flash()->error('Impossible de régénérer le lien. Veuillez réessayer.');

            return back();
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PayLink $paylink)
    {

        return Inertia::render('Admin/Paybylink/Update', compact('paylink'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StorePayLinkRequest $request, PayLink $paylink)
    {
        $paylink->update($request->validated());
        if ($paylink->wasChanged('montant')) {
            $currencyCode = 'XOF';
            $emailAddress = '';
            $redirectUrl = route('home');
            $cancelUrl = route('home');

            $accessToken = $this->getAccessToken();
            if ($accessToken) {
                $postData = $this->prepareTransactionData($request->integer('montant'), $currencyCode, $emailAddress, $redirectUrl, $cancelUrl);
                $response = $this->createOrder($postData, $accessToken);
                if ($response && isset($response['_links']['payment']['href'])) {
                    $link = $response['_links']['payment']['href'];
                    // save temporaly order
                    $paylink->update(['lien' => $link, 'trans_ref' => $response['reference']]);
                }
            }
        }

        return back();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PayLink $paylink)
    {
        return $this->supp($paylink);
    }
}
