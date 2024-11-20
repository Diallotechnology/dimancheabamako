<?php

declare(strict_types=1);

namespace App\Helper;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

trait OrderAPI
{
    // https://api-gateway.sandbox.ngenius-payments.com
    // https://api-gateway.orabankml.ngenius-payments.com
    protected string $base = 'https://api-gateway.orabankml.ngenius-payments.com';

    private function cancelPayment(string $orderReference)
    {
        $outlet = env('NGENIUS_OUTLET_ID');
        $responseData = $this->getOrderStatut($orderReference);
        $payment = $responseData['_embedded']['payment'][0]['reference'];

        return Http::withHeaders([
            'Authorization' => 'Bearer '.$this->getAccessToken(),
            'Content-Type' => 'application/vnd.ni-payment.v2+json',
            'Accept' => 'application/vnd.ni-payment.v2+json',
        ])->put($this->base.'/transactions/outlets/'.$outlet.'/orders/'.$orderReference.'/payments/'.$payment.'/cancel');
    }

    private function cancelPaymentLink(string $orderReference)
    {
        $outlet = env('NGENIUS_OUTLET_ID');

        return Http::withHeaders([
            'Authorization' => 'Bearer '.$this->getAccessToken(),
            'Content-Type' => 'application/vnd.ni-payment.v2+json',
            'Accept' => 'application/vnd.ni-payment.v2+json',
        ])->put($this->base.'/transactions/outlets/'.$outlet.'/orders/'.$orderReference.'/cancel');
    }

    private function getOrderStatut(string $reference)
    {
        $accessToken = $this->getAccessToken();
        $outlet = env('NGENIUS_OUTLET_ID');
        // L'URL de l'API pour récupérer les détails de la commande
        $url = $this->base.'/transactions/outlets/'.$outlet.'/orders/'.$reference;
        $response = Http::withHeaders([
            'Authorization' => 'Bearer '.$accessToken,
            'Content-Type' => 'application/vnd.ni-payment.v2+json',
            'Accept' => 'application/vnd.ni-payment.v2+json',
        ])->get($url);

        if ($response->successful()) {
            return $response->json();
        } else {
            Log::error('Failed to get transaction details', ['reference' => $reference]);

            return null;
        }
    }

    private function getAccessToken()
    {
        $response = Http::withHeaders([
            'accept' => 'application/vnd.ni-identity.v1+json',
            'authorization' => 'Basic '.env('NGENIUS_API_KEY'),
            'content-type' => 'application/vnd.ni-identity.v1+json',
        ])->post($this->base.'/identity/auth/access-token', [
            'realmName' => env('NGENIUS_REALM_NAME'),
        ]);

        if ($response->successful()) {
            return $response->json()['access_token'];
        } else {
            Log::error('Failed to get access token');

            return null;
        }
    }

    private function prepareTransactionData($montant, $currencyCode, $emailAddress, $redirectUrl, $cancelUrl, $lang = null)
    {
        return [
            'action' => 'PURCHASE',
            'language' => $lang ?? session('locale'),
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
            ->post($this->base.'/transactions/outlets/'.$outlet.'/orders', $postData);

        if ($response->successful()) {
            return $response->json();
        } else {
            Log::error('Failed to create order', ['response' => $response->json()]);

            return null;
        }
    }
}
