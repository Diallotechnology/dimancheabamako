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
    // protected string $base = env('NGENIUS_API_KEY');
    // protected string $base =  config('services.api_key');

    protected function base(): string
    {
        return config('services.ngenius.base_url');
    }

    protected function outletId(): string
    {
        return config('services.ngenius.outlet_id');
    }

    protected function getHeaders(): array
    {
        return [
            'Authorization' => 'Bearer ' . $this->getAccessToken(),
            'Content-Type' => 'application/vnd.ni-payment.v2+json',
            'Accept' => 'application/vnd.ni-payment.v2+json',
        ];
    }

    public function getOrderStatut(string $reference)
    {
        $url = $this->base() . "/transactions/outlets/" . $this->outletId() . "/orders/{$reference}";

        $response = Http::withHeaders($this->getHeaders())->get($url);

        if ($response->successful()) {
            return $response->json();
        }

        Log::error('Failed to get transaction details', [
            'reference' => $reference,
            'status' => $response->status(),
            'message' => $response->json() ?? 'No response body',
        ]);
        throw new \Exception("Erreur lors de la récupération du statut de la commande : " . ($response->json()['message'] ?? 'Erreur inconnue'));
    }

    private function getAccessToken(): string
    {
        $cachedToken = Cache::get('ngenius_access_token');
        if ($cachedToken) {
            return $cachedToken;
        }

        $response = Http::withHeaders([
            'accept' => 'application/vnd.ni-identity.v1+json',
            'authorization' => 'Basic ' . config('services.ngenius.api_key'),
            'content-type' => 'application/vnd.ni-identity.v1+json',
        ])->post($this->base() . '/identity/auth/access-token', [
            'realmName' => config('services.ngenius.realm_name'),
        ]);

        if ($response->successful()) {
            $token = $response->json()['access_token'];
            Cache::put('ngenius_access_token', $token, $response->json()['expires_in'] - 60);

            return $token;
        }

        Log::error('Failed to get access token');
        throw new \Exception('Impossible d’obtenir un jeton d’accès depuis l’API NGENIUS');
    }

    public function cancelPayment(string $orderReference)
    {
        $responseData = $this->getOrderStatut($orderReference);
        $payment = $responseData['_embedded']['payment'][0]['reference'] ?? null;

        if (! $payment) {
            Log::error('Payment reference not found', ['orderReference' => $orderReference]);
            throw new \Exception('Référence de paiement introuvable dans les données de la commande');
        }

        $url = $this->base() . "/transactions/outlets/" . $this->outletId() . "/orders/{$orderReference}/payments/{$payment}/cancel";
        $response = Http::withHeaders($this->getHeaders())->put($url);

        if ($response->successful()) {
            return $response->json();
        }

        Log::error('Failed to cancel payment', ['orderReference' => $orderReference, 'response' => $response->json()]);
        throw new \Exception('Impossible d’annuler le paiement');
    }

    public function cancelPaymentLink(string $orderReference)
    {
        $url = $this->base() . "/transactions/outlets/" . $this->outletId() . "/orders/{$orderReference}/cancel";
        $response = Http::withHeaders($this->getHeaders())->put($url);

        if ($response->successful()) {
            return $response->json();
        }

        Log::error('Failed to cancel payment link', ['orderReference' => $orderReference, 'response' => $response->json()]);
        throw new \Exception('Impossible d’annuler le lien de paiement');
    }

    public function prepareTransactionData(int $montant, string $currencyCode, string $emailAddress, string $redirectUrl, string $cancelUrl, $lang = null): array
    {
        if (! is_int($montant)) {
            throw new \InvalidArgumentException('Le montant doit être un entier représentant la valeur en centimes.');
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
        $url = $this->base() . "/transactions/outlets/" . $this->outletId() . "/orders";

        $response = Http::withToken($token)
            ->withHeaders([
                'Content-Type' => 'application/vnd.ni-payment.v2+json',
                'Accept' => 'application/vnd.ni-payment.v2+json',
            ])
            ->post($url, $postData);

        if ($response->successful()) {
            return $response->json();
        }

        Log::error('Failed to create order', ['response' => $response->json()]);
        throw new \Exception('Impossible de créer la commande');
    }
}
