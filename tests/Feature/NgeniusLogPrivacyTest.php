<?php

declare(strict_types=1);

use App\Enum\RoleEnum;
use App\Helper\OrderAPI;
use App\Models\Client;
use App\Models\Country;
use App\Models\Order;
use App\Models\Transport;
use App\Models\User;
use App\Models\Zone;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

// I5 — rien de personnel (e-mail, adresse, titulaire, carte) ne doit finir dans laravel.log

const PII = ['awa@example.com', 'Traoré', 'ACI 2000', 'AWA TRAORE', '411111'];

function ngeniusPayload(array $payment): array
{
    return [
        'reference' => 'ref-1',
        'emailAddress' => 'awa@example.com',
        'billingAddress' => ['firstName' => 'Awa', 'lastName' => 'Traoré', 'address1' => 'ACI 2000'],
        '_embedded' => ['payment' => [array_merge([
            'paymentMethod' => ['pan' => '411111******1111', 'cardholderName' => 'AWA TRAORE'],
        ], $payment)]],
    ];
}

function fakeNgeniusOrder(array $payload): void
{
    Http::fake([
        '*/identity/auth/access-token' => Http::response(['access_token' => 'tok', 'expires_in' => 3600]),
        '*/orders/ref-1' => Http::response($payload),
        '*/orders' => Http::response(['message' => 'Invalid redirect url'], 422),
        '*' => Http::response([]),
    ]);
}

/** Vrai si un log du niveau donné a été émis sans aucune donnée personnelle dans son contexte. */
function loggedWithoutPii(string $level): bool
{
    try {
        Log::shouldHaveReceived($level)->withArgs(function ($message, $context = []) {
            $json = json_encode($context, JSON_UNESCAPED_UNICODE);

            foreach (PII as $needle) {
                if (str_contains((string) $json, $needle)) {
                    return false;
                }
            }

            return true;
        })->atLeast()->once();
    } catch (Throwable) {
        return false;
    }

    return true;
}

function ngeniusClient(): object
{
    return new class
    {
        use OrderAPI;
    };
}

it('logs no personal data when the order payload has no payment block', function () {
    Log::spy();
    fakeNgeniusOrder(['reference' => 'ref-1', 'emailAddress' => 'awa@example.com', 'billingAddress' => ['address1' => 'ACI 2000', 'lastName' => 'Traoré']]);

    ngeniusClient()->getOrderStatus('ref-1');

    expect(loggedWithoutPii('error'))->toBeTrue();
});

it('logs no personal data when the payment reference is missing on cancel', function () {
    Log::spy();
    fakeNgeniusOrder(ngeniusPayload(['state' => 'STARTED']));

    expect(fn () => ngeniusClient()->cancelPayment('ref-1'))->toThrow(Exception::class);

    expect(loggedWithoutPii('error'))->toBeTrue();
});

it('logs no personal data when check-payment finds no payment state', function () {
    Log::spy();
    fakeNgeniusOrder(ngeniusPayload([]));
    Order::factory()->create([
        'client_id' => Client::factory()->create()->id,
        'country_id' => Country::factory()->create(['zone_id' => Zone::factory()->create()->id])->id,
        'transport_id' => Transport::factory()->create()->id,
        'trans_ref' => 'ref-1',
        'reference' => null,
    ]);

    Artisan::call('app:check-payment');

    expect(loggedWithoutPii('warning'))->toBeTrue();
});

it('does not log stack traces when pay-link creation fails', function () {
    Log::spy();
    fakeNgeniusOrder([]);
    $admin = User::factory()->create(['role' => RoleEnum::ADMIN]);

    $this->actingAs($admin)->post('/admin/paylink', ['name' => 'Awa', 'contact' => '+22370123456', 'montant' => 5000]);

    Log::shouldHaveReceived('error')
        ->withArgs(fn ($message, $context = []) => $message === 'Erreur PayLink::store()' && ! array_key_exists('trace', $context))
        ->once();
});
