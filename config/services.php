<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],
    'ai' => [
        // Fournisseur IA vision des fiches produit : mistral | gemini | ollama
        'product_provider' => env('AI_PRODUCT_PROVIDER', 'mistral'),
    ],
    'mistral' => [
        'key' => env('MISTRAL_API_KEY'),
        'model' => env('MISTRAL_MODEL', 'pixtral-large-latest'),
        'base_url' => env('MISTRAL_BASE_URL', 'https://api.mistral.ai/v1'),
    ],
    'ollama' => [
        'base_url' => env('OLLAMA_BASE_URL', 'http://localhost:11434'),
        'model' => env('OLLAMA_MODEL', 'llama3.2-vision'),
        // Un modèle vision local est lent (surtout au premier chargement) : timeout généreux, en secondes.
        'timeout' => (int) env('OLLAMA_TIMEOUT', 120),
        // Contexte du modèle : une photo coûte ~4000 tokens, le défaut Ollama (4096) tronque la réponse.
        'num_ctx' => (int) env('OLLAMA_NUM_CTX', 8192),
    ],
    'gemini' => [
        'key' => env('GEMINI_API_KEY'),
        'model' => env('GEMINI_MODEL', 'gemini-2.0-flash'),
        'base_url' => env('GEMINI_BASE_URL', 'https://generativelanguage.googleapis.com/v1beta'),
    ],
    'ngenius' => [
        'api_key' => env('NGENIUS_API_KEY'),
        'outlet_id' => env('NGENIUS_OUTLET_ID'),
        'realm_name' => env('NGENIUS_REALM_NAME'),
        'base_url' => env('NGENIUS_BASE_URL', 'https://api-gateway.sandbox.ngenius-payments.com'),
    ],

];
