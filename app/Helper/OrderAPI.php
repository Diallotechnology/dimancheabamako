<?php

declare(strict_types=1);

namespace App\Helper;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

trait OrderAPI
{
    private function getOrderStatut(string $reference)
    {
        $accessToken = $this->getAccessToken();
        $outlet = env('NGENIUS_OUTLET_ID');
        // L'URL de l'API pour récupérer les détails de la commande
        $url = 'https://api-gateway.sandbox.ngenius-payments.com/transactions/outlets/'.$outlet.'/orders/'.$reference;
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
        ])->post('https://api-gateway.sandbox.ngenius-payments.com/identity/auth/access-token', [
            'realmName' => env('NGENIUS_REALM_NAME'),
        ]);

        if ($response->successful()) {
            return $response->json()['access_token'];
        } else {
            Log::error('Failed to get access token', ['response' => $response->json()]);

            return null;
        }
    }
}
