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
            ]);

            return back()->withErrors(['ngenius' => 'Impossible de créer le lien de paiement.']);
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

            return back()->withErrors(['ngenius' => 'Impossible de régénérer le lien. Veuillez réessayer.']);
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
        DB::beginTransaction();

        try {
            $paylink->update($request->validated());

            if ($paylink->wasChanged('montant')) {
                $postData = $this->prepareTransactionData(
                    $request->integer('montant'),
                    'XOF',
                    '',
                    route('home'),
                    route('home')
                );

                $response = $this->createOrder($postData, $this->getAccessToken());

                if (! isset($response['_links']['payment']['href'], $response['reference'])) {
                    throw new Exception('Réponse API invalide : lien de paiement absent');
                }

                // Montant et lien changent ensemble ou pas du tout : jamais un lien périmé à l'ancien montant.
                $paylink->update([
                    'lien' => $response['_links']['payment']['href'],
                    'trans_ref' => $response['reference'],
                ]);
            }

            DB::commit();

            return back();
        } catch (Throwable $e) {
            DB::rollBack();

            Log::error('Erreur PayLink::update()', [
                'paylink_id' => $paylink->id,
                'message' => $e->getMessage(),
            ]);

            return back()->withErrors(['ngenius' => 'Le nouveau lien n’a pas pu être généré : le lien et le montant n’ont pas été modifiés.']);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PayLink $paylink)
    {
        return $this->supp($paylink);
    }
}
