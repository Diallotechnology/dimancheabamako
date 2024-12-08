<?php

declare(strict_types=1);

namespace App\Helper;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

trait OrderAPI
{
    // https://api-gateway.sandbox.ngenius-payments.com
    // https://api-gateway.orabankml.ngenius-payments.com

    protected string $base = 'https://api-gateway.sandbox.ngenius-payments.com';

    private function getAccessToken(): string
    {
        $cachedToken = Cache::get('ngenius_access_token');
        if ($cachedToken) {
            return $cachedToken;
        }

        $response = Http::withHeaders([
            'accept' => 'application/vnd.ni-identity.v1+json',
            'authorization' => 'Basic '.env('NGENIUS_API_KEY'),
            'content-type' => 'application/vnd.ni-identity.v1+json',
        ])->post($this->base.'/identity/auth/access-token', [
            'realmName' => env('NGENIUS_REALM_NAME'),
        ]);

        if ($response->successful()) {
            $token = $response->json()['access_token'];
            Cache::put('ngenius_access_token', $token, $response->json()['expires_in'] - 60);

            return $token;
        }

        Log::error('Failed to get access token');
        throw new \Exception('Unable to obtain access token from NGENIUS API');
    }

    public function getOrderStatut(string $reference)
    {
        $url = $this->base.'/transactions/outlets/'.env('NGENIUS_OUTLET_ID')."/orders/{$reference}";
        $response = Http::withHeaders([
            'Authorization' => 'Bearer '.$this->getAccessToken(),
            'Content-Type' => 'application/vnd.ni-payment.v2+json',
            'Accept' => 'application/vnd.ni-payment.v2+json',
        ])->get($url);

        if ($response->successful()) {
            return $response->json();
        }

        Log::error('Failed to get transaction details', ['reference' => $reference, 'response' => $response->json()]);
        throw new \Exception('Unable to fetch order status');
    }

    public function cancelPayment(string $orderReference)
    {
        $responseData = $this->getOrderStatut($orderReference);
        $payment = $responseData['_embedded']['payment'][0]['reference'] ?? null;

        if (! $payment) {
            Log::error('Payment reference not found', ['orderReference' => $orderReference]);
            throw new \Exception('Payment reference missing in order data');
        }

        $url = $this->base.'/transactions/outlets/'.env('NGENIUS_OUTLET_ID')."/orders/{$orderReference}/payments/{$payment}/cancel";
        $response = Http::withHeaders([
            'Authorization' => 'Bearer '.$this->getAccessToken(),
            'Content-Type' => 'application/vnd.ni-payment.v2+json',
            'Accept' => 'application/vnd.ni-payment.v2+json',
        ])->put($url);

        if ($response->successful()) {
            return $response->json();
        }

        Log::error('Failed to cancel payment', ['orderReference' => $orderReference, 'response' => $response->json()]);
        throw new \Exception('Unable to cancel payment');
    }

    public function cancelPaymentLink(string $orderReference)
    {
        $outlet = env('NGENIUS_OUTLET_ID');
        $url = $this->base."/transactions/outlets/{$outlet}/orders/{$orderReference}/cancel";

        $response = Http::withHeaders([
            'Authorization' => 'Bearer '.$this->getAccessToken(),
            'Content-Type' => 'application/vnd.ni-payment.v2+json',
            'Accept' => 'application/vnd.ni-payment.v2+json',
        ])->put($url);

        if ($response->successful()) {
            return $response->json();
        }

        Log::error('Failed to cancel payment link', ['orderReference' => $orderReference, 'response' => $response->json()]);
        throw new \Exception('Unable to cancel payment link');
    }

    public function prepareTransactionData(int $montant, string $currencyCode, string $emailAddress, string $redirectUrl, string $cancelUrl, $lang = null): array
    {
        if (! is_int($montant)) {
            throw new \InvalidArgumentException('Amount must be an integer representing the value in cents.');
        }

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

    public function createOrder(array $postData, string $token)
    {
        $outlet = env('NGENIUS_OUTLET_ID');
        $response = Http::withToken($token)
            ->withHeaders([
                'Content-Type' => 'application/vnd.ni-payment.v2+json',
                'Accept' => 'application/vnd.ni-payment.v2+json',
            ])
            ->post($this->base."/transactions/outlets/{$outlet}/orders", $postData);

        if ($response->successful()) {
            return $response->json();
        }

        Log::error('Failed to create order', ['response' => $response->json()]);
        throw new \Exception('Unable to create order');
    }
}
