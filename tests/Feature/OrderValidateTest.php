<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

// order.validate : N-Genius redirige ici quel que soit le résultat du paiement.
// La page ne doit afficher « succès » (et vider le panier) que si N-Genius confirme PURCHASED
// pour la commande de CETTE session.

const VALIDATE_REF = 'ngenius-ref-123';

function fakeNgeniusPayment(?string $state, int $status = 200): void
{
    Http::fake([
        '*/identity/auth/access-token' => Http::response(['access_token' => 'tok', 'expires_in' => 3600]),
        '*/orders/'.VALIDATE_REF => Http::response(
            $state === null ? [] : ['_embedded' => ['payment' => [['state' => $state]]]],
            $status,
        ),
        '*' => Http::response([]),
    ]);
}

/**
 * Acheteur connecté (clé de panier stable : l'id de session d'un invité change pendant la requête)
 * avec référence en attente et panier non vide. Retourne la clé du panier.
 */
function buyerSessionWithCart(object $test, string $pending = VALIDATE_REF): string
{
    $user = User::factory()->create();
    $test->actingAs($user)->withSession(['pending_order_ref' => $pending]);
    $key = "cart_user_{$user->id}";
    Cache::put($key, [1 => ['quantity' => 1]]);

    return $key;
}

function statusCalls(): int
{
    return Http::recorded(fn ($request) => str_contains($request->url(), '/orders/'.VALIDATE_REF))->count();
}

it('shows success and clears the cart only when N-Genius confirms PURCHASED', function () {
    fakeNgeniusPayment('PURCHASED');
    $cartKey = buyerSessionWithCart($this);

    $this->get('/order/validate?ref='.VALIDATE_REF)
        ->assertOk()
        ->assertSee(__('messages.order_success'))
        ->assertSessionMissing('pending_order_ref');

    expect(Cache::has($cartKey))->toBeFalse();
});

it('shows a failure and keeps the cart when the payment failed', function (string $state) {
    fakeNgeniusPayment($state);
    $cartKey = buyerSessionWithCart($this);

    $this->get('/order/validate?ref='.VALIDATE_REF)
        ->assertOk()
        ->assertDontSee(__('messages.order_success'))
        ->assertSee(__('messages.payment_failed'))
        ->assertSessionMissing('pending_order_ref');

    expect(Cache::has($cartKey))->toBeTrue();
})->with(['FAILED', 'CANCELLED', 'EXPIRED']);

it('shows a neutral page without calling N-Genius when the ref is missing or not the session one', function (string $url) {
    fakeNgeniusPayment('PURCHASED');
    $cartKey = buyerSessionWithCart($this);

    $this->get($url)
        ->assertOk()
        ->assertDontSee(__('messages.order_success'))
        ->assertSee(__('messages.payment_pending'));

    expect(Cache::has($cartKey))->toBeTrue()
        ->and(statusCalls())->toBe(0);
})->with(['/order/validate', '/order/validate?ref=someone-else']);

it('shows a neutral page from a session that did not place the order', function () {
    fakeNgeniusPayment('PURCHASED');

    $this->get('/order/validate?ref='.VALIDATE_REF)
        ->assertOk()
        ->assertDontSee(__('messages.order_success'))
        ->assertSee(__('messages.payment_pending'));

    expect(statusCalls())->toBe(0);
});

it('shows a neutral page and keeps the cart when the gateway errors or the state is unknown', function (?string $state, int $status) {
    fakeNgeniusPayment($state, $status);
    $cartKey = buyerSessionWithCart($this);

    $this->get('/order/validate?ref='.VALIDATE_REF)
        ->assertOk()
        ->assertDontSee(__('messages.order_success'))
        ->assertSee(__('messages.payment_pending'))
        ->assertSessionHas('pending_order_ref', VALIDATE_REF);

    expect(Cache::has($cartKey))->toBeTrue();
})->with([
    'gateway error' => [null, 500],
    'no payment yet' => [null, 200],
    'unknown state' => ['STARTED', 200],
]);
