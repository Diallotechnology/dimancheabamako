<?php

declare(strict_types=1);

namespace App\Helper;

use Exception;
use Illuminate\Http\Client\Response;
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
            Log::error('NGENIUS: Failed to fetch order status', ['url' => $url] + $this->ngeniusErrorContext($response));
            throw new Exception('Erreur API NGENIUS : impossible de récupérer le statut de la commande.');
        }

        $data = $response->json();

        if (! isset($data['_embedded']['payment'])) {
            Log::error('NGENIUS: Missing payment embedded data', $this->ngeniusLogContext($data));
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
                'order' => $this->ngeniusLogContext($order),
            ]);
            throw new Exception('Impossible de trouver la référence du paiement.');
        }

        $url = $this->endpoint("orders/{$orderReference}/payments/{$payment}/cancel");

        $response = $this->api()->put($url);

        if (! $response->successful()) {
            Log::error('NGENIUS: Payment cancel error', [
                'url' => $url,
                'orderReference' => $orderReference,
            ] + $this->ngeniusErrorContext($response));
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
            ] + $this->ngeniusErrorContext($response));
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
            // throw: false — sinon retry() lève une RequestException et la branche ci-dessous (log du body) n'est jamais atteinte.
            ->retry(2, 200, throw: false)
            ->withToken($token)
            ->withHeaders([
                'Accept' => 'application/vnd.ni-payment.v2+json',
                'Content-Type' => 'application/vnd.ni-payment.v2+json',
            ])
            ->post($url, $postData);

        if (! $response->successful()) {
            Log::error('NGENIUS: Create order failed', $this->ngeniusErrorContext($response));
            throw new Exception("Création de commande impossible via NGENIUS (HTTP {$response->status()}).");
        }

        return $response->json();
    }

    /**
     * Résumé d'une réponse N-Genius pour les logs : jamais le payload brut
     * (e-mail, adresse de facturation, titulaire et numéro de carte masqué).
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function ngeniusLogContext(array $data): array
    {
        $payments = data_get($data, '_embedded.payment', []);

        return [
            'reference' => data_get($data, 'reference'),
            'payment_states' => is_array($payments) ? array_column($payments, 'state') : [],
            'keys' => array_keys($data),
        ];
    }

    /**
     * Contexte de log d'une réponse N-Genius refusée : statut + body d'erreur (jamais de payload de succès).
     *
     * @return array<string, mixed>
     */
    protected function ngeniusErrorContext(Response $response): array
    {
        return [
            'status' => $response->status(),
            'body' => $response->json() ?? $response->body(),
        ];
    }

    /** BASE REQUEST (timeout + retry + headers) */
    protected function api()
    {
        // throw: false — sinon retry() lève une RequestException avant les `! successful()` des appelants
        // (log du body jamais atteint). Les appelants lèvent leur propre Exception après avoir loggé.
        return Http::timeout(10)
            ->retry(2, 200, throw: false)
            ->withHeaders($this->getHeaders());
    }

    /** URL BUILDER */
    protected function endpoint(string $path): string
    {
        return mb_rtrim($this->base(), '/').'/transactions/outlets/'.$this->outletId().'/'.$path;
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
            'Authorization' => 'Bearer '.$this->getAccessToken(),
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
            ->retry(1, 100, throw: false)
            ->withHeaders([
                'Accept' => 'application/vnd.ni-identity.v1+json',
                'Authorization' => 'Basic '.config('services.ngenius.api_key'),
                'Content-Type' => 'application/vnd.ni-identity.v1+json',
            ])
            ->post($this->base().'/identity/auth/access-token', [
                'realmName' => config('services.ngenius.realm_name'),
            ]);

        if (! $response->successful()) {
            Log::error('NGENIUS: Failed to get access token', $this->ngeniusErrorContext($response));
            throw new Exception('Impossible d’obtenir un jeton NGENIUS.');
        }

        $data = $response->json();

        $token = $data['access_token'] ?? null;
        $expires = $data['expires_in'] ?? 3600;

        Cache::put('ngenius_access_token', $token, $expires - 60);

        return $token;
    }
}
