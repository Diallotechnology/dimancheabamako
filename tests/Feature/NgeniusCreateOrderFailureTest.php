<?php

declare(strict_types=1);

use App\Helper\OrderAPI;
use App\Providers\AppServiceProvider;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

it('logs the N-Genius response body when order creation is rejected', function () {
    Log::spy();
    Http::fake(['*' => Http::response(['message' => 'Invalid redirect url', 'code' => 422], 422)]);

    $client = new class
    {
        use OrderAPI;
    };

    // Plus de RequestException opaque : une Exception métier qui porte le statut HTTP.
    expect(fn () => $client->createOrder(['action' => 'PURCHASE'], 'tok'))
        ->toThrow(Exception::class, 'HTTP 422');

    Log::shouldHaveReceived('error')
        ->withArgs(fn ($message, $context = []) => $message === 'NGENIUS: Create order failed'
            && $context['status'] === 422
            && ($context['body']['message'] ?? null) === 'Invalid redirect url')
        ->once();
});

it('builds URLs from APP_URL in local env (tunnel HTTPS)', function () {
    config(['app.url' => 'https://tunnel.example.com']);
    app()->detectEnvironment(fn () => 'local');

    (new AppServiceProvider(app()))->boot();

    expect(url('/panier'))->toBe('https://tunnel.example.com/panier');
});
