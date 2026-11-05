<?php

declare(strict_types=1);

use App\Helper\OrderAPI;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

// Les appels via api() (et le jeton) doivent logger statut + body N-Genius quand la passerelle refuse :
// avec retry() sans throw: false, la RequestException court-circuitait ces logs.

function apiFailureClient(): object
{
    return new class
    {
        use OrderAPI;
    };
}

function fakeNgeniusRefusal(string $failing): void
{
    $paymentOrder = ['_embedded' => ['payment' => [['reference' => 'pay-1', 'state' => 'STARTED']]]];

    Http::fake([
        '*/identity/auth/access-token' => $failing === 'token'
            ? Http::response(['message' => 'boom'], 422)
            : Http::response(['access_token' => 'tok', 'expires_in' => 3600]),
        '*/payments/pay-1/cancel' => Http::response(['message' => 'boom'], 422),
        '*/orders/ref-1/cancel' => Http::response(['message' => 'boom'], 422),
        '*/orders/ref-1' => $failing === 'status'
            ? Http::response(['message' => 'boom'], 422)
            : Http::response($paymentOrder),
        '*' => Http::response([]),
    ]);
}

it('logs status and body when N-Genius refuses', function (string $failing, string $logMessage, Closure $call) {
    Cache::forget('ngenius_access_token');
    Log::spy();
    fakeNgeniusRefusal($failing);

    expect(fn () => $call(apiFailureClient()))->toThrow(Exception::class);

    Log::shouldHaveReceived('error')
        ->withArgs(fn ($message, $context = []) => $message === $logMessage
            && ($context['status'] ?? null) === 422
            && ($context['body']['message'] ?? null) === 'boom')
        ->once();
})->with([
    'order status' => ['status', 'NGENIUS: Failed to fetch order status', fn ($c) => $c->getOrderStatus('ref-1')],
    'cancel payment' => ['cancel', 'NGENIUS: Payment cancel error', fn ($c) => $c->cancelPayment('ref-1')],
    'cancel payment link' => ['cancel', 'NGENIUS: Cancel payment link failed', fn ($c) => $c->cancelPaymentLink('ref-1')],
    'access token' => ['token', 'NGENIUS: Failed to get access token', fn ($c) => $c->getOrderStatus('ref-1')],
]);
