<?php

declare(strict_types=1);

namespace App\Helper;

use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

trait OrderAPI
{
        // https://api-gateway.sandbox.ngenius-payments.com
    // https://api-gateway.orabankml.ngenius-payments.com
    // protected string $base = env('NGENIUS_API_KEY');
    // protected string $base =  config('services.api_key');

    /** GET ORDER STATUS */
    public function getOrderStatus(string $reference): array
    {
        $url = $this->endpoint("orders/{$reference}");

        $response = $this->api()->get($url);

        if (! $response->successful()) {
            Log::error('NGENIUS: Failed to fetch order status', [
                'url' => $url,
                'status' => $response->status(),
                'body' => $response->json(),
            ]);
            throw new Exception('Erreur API NGENIUS : impossible de récupérer le statut de la commande.');
        }

        $data = $response->json();

        if (! isset($data['_embedded']['payment'])) {
            Log::error('NGENIUS: Missing payment embedded data', ['response' => $data]);
        }

        return $data;
    }

    /** CANCEL PAYMENT */
    public function cancelPayment(string $orderReference): array
    {
        $order = $this->getOrderStatus($orderReference);

        $payment = $order['_embedded']['payment'][0]['reference'] ?? null;

        if (! $payment) {
            Log::error('NGENIUS: Payment reference missing', [
                'orderReference' => $orderReference,
                'order' => $order,
            ]);
            throw new Exception('Impossible de trouver la référence du paiement.');
        }

        $url = $this->endpoint("orders/{$orderReference}/payments/{$payment}/cancel");

        $response = $this->api()->put($url);

        if (! $response->successful()) {
            Log::error('NGENIUS: Payment cancel error', [
                'url' => $url,
                'orderReference' => $orderReference,
                'body' => $response->json(),
            ]);
            throw new Exception('L’API NGENIUS a refusé l’annulation du paiement.');
        }

        return $response->json();
    }

    /** CANCEL PAYMENT LINK */
    public function cancelPaymentLink(string $orderReference): array
    {
        $url = $this->endpoint("orders/{$orderReference}/cancel");

        $response = $this->api()->put($url);

        if (! $response->successful()) {
            Log::error('NGENIUS: Cancel payment link failed', [
                'orderReference' => $orderReference,
                'body' => $response->json(),
            ]);
            throw new Exception("Impossible d'annuler le lien de paiement.");
        }

        return $response->json();
    }

    /** PREPARE TRANSACTION */
    public function prepareTransactionData(int $montant, string $currency, string $email, string $redirectUrl, string $cancelUrl, $lang = null): array
    {
        if ($montant < 1) {
            throw new InvalidArgumentException('Montant invalide pour NGENIUS.');
        }

        return [
            'action' => 'PURCHASE',
            'language' => $lang ?? session('locale'),
            'emailAddress' => $email,
            'amount' => [
                'currencyCode' => $currency,
                'value' => $montant,
            ],
            'merchantAttributes' => [
                'redirectUrl' => $redirectUrl,
                'cancelUrl' => $cancelUrl,
                'cancelText' => session('locale') === 'fr'
                    ? 'Continuer mes achats'
                    : 'Continue Shopping',
            ],
        ];
    }

    /** CREATE ORDER */
    public function createOrder(array $postData, string $token)
    {
        $url = $this->endpoint('orders');

        $response = Http::timeout(10)
            ->retry(2, 200)
            ->withToken($token)
            ->withHeaders([
                'Accept' => 'application/vnd.ni-payment.v2+json',
                'Content-Type' => 'application/vnd.ni-payment.v2+json',
            ])
            ->post($url, $postData);

        if (! $response->successful()) {
            Log::error('NGENIUS: Create order failed', [
                'body' => $response->json(),
            ]);
            throw new Exception('Création de commande impossible via NGENIUS.');
        }

        return $response->json();
    }


    /** BASE REQUEST (timeout + retry + headers) */
    protected function api()
    {
        return Http::timeout(10)
            ->retry(2, 200)
            ->withHeaders($this->getHeaders());
    }

    /** URL BUILDER */
    protected function endpoint(string $path): string
    {
        return mb_rtrim($this->base(), '/') . '/transactions/outlets/' . $this->outletId() . '/' . $path;
    }

    /** BASE URL */
    protected function base(): string
    {
        return config('services.ngenius.base_url');
    }

    /** OUTLET */
    protected function outletId(): string
    {
        return config('services.ngenius.outlet_id');
    }

    /** HEADERS */
    protected function getHeaders(): array
    {
        return [
            'Authorization' => 'Bearer ' . $this->getAccessToken(),
            'Accept' => 'application/vnd.ni-payment.v2+json',
            'Content-Type' => 'application/vnd.ni-payment.v2+json',
        ];
    }

    /** TOKEN */
    private function getAccessToken(): string
    {
        if ($token = Cache::get('ngenius_access_token')) {
            return $token;
        }

        $response = Http::timeout(10)
            ->retry(1, 100)
            ->withHeaders([
                'Accept' => 'application/vnd.ni-identity.v1+json',
                'Authorization' => 'Basic ' . config('services.ngenius.api_key'),
                'Content-Type' => 'application/vnd.ni-identity.v1+json',
            ])
            ->post($this->base() . '/identity/auth/access-token', [
                'realmName' => config('services.ngenius.realm_name'),
            ]);

        if (! $response->successful()) {
            Log::error('NGENIUS: Failed to get access token', [
                'body' => $response->json(),
            ]);
            throw new Exception('Impossible d’obtenir un jeton NGENIUS.');
        }

        $data = $response->json();

        $token = $data['access_token'] ?? null;
        $expires = $data['expires_in'] ?? 3600;

        Cache::put('ngenius_access_token', $token, $expires - 60);

        return $token;
    }
}
