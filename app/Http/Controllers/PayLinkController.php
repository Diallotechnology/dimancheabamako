<?php

namespace App\Http\Controllers;

use App\Helper\DeleteAction;
use App\Helper\OrderAPI;
use App\Http\Requests\StorePayLinkRequest;
use App\Models\PayLink;
use Inertia\Inertia;

class PayLinkController extends Controller
{
    use DeleteAction, OrderAPI;

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePayLinkRequest $request)
    {

        $montant = $request->montant;
        $currencyCode = 'XOF';
        $emailAddress = '';
        $redirectUrl = route('home');
        $cancelUrl = route('home');

        $accessToken = $this->getAccessToken();
        if ($accessToken) {
            $postData = $this->prepareTransactionData($montant, $currencyCode, $emailAddress, $redirectUrl, $cancelUrl, 'fr');
            $response = $this->createOrder($accessToken, $postData);
            if ($response && isset($response['_links']['payment']['href'])) {
                $link = $response['_links']['payment']['href'];
                // save temporaly order
                $data = $request->validated();
                $data['lien'] = $link;
                $data['trans_ref'] = $response['reference'];
                PayLink::create($data);

            }
        }

        return back();
    }

    public function regenerate(PayLink $paylink)
    {
        $currencyCode = 'XOF';
        $emailAddress = '';
        $redirectUrl = route('home');
        $cancelUrl = route('home');

        $accessToken = $this->getAccessToken();
        if ($accessToken) {
            $postData = $this->prepareTransactionData($paylink->montant, $currencyCode, $emailAddress, $redirectUrl, $cancelUrl);
            $response = $this->createOrder($accessToken, $postData);
            if ($response && isset($response['_links']['payment']['href'])) {
                $link = $response['_links']['payment']['href'];
                // save temporaly order
                $paylink->update(['lien' => $link, 'trans_ref' => $response['reference']]);

            }
        }

        return \back();
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
                $postData = $this->prepareTransactionData($request->montant, $currencyCode, $emailAddress, $redirectUrl, $cancelUrl);
                $response = $this->createOrder($accessToken, $postData);
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
