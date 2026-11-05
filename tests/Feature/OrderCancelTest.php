<?php

declare(strict_types=1);

use App\Models\Client;
use App\Models\Country;
use App\Models\Order;
use App\Models\Transport;
use App\Models\Zone;
use Illuminate\Support\Facades\Http;

// I2 — GET order/cancel : seule la session qui a passé la commande peut l'annuler, et seulement si elle est impayée

function unpaidOrder(array $overrides = []): Order
{
    return Order::factory()->create(array_merge([
        'client_id' => Client::factory()->create()->id,
        'country_id' => Country::factory()->create(['zone_id' => Zone::factory()->create()->id])->id,
        'transport_id' => Transport::factory()->create()->id,
        'trans_ref' => 'ngenius-ref-123',
        'reference' => null,
        'trans_state' => null,
    ], $overrides));
}

function fakeNgenius(int $cancelStatus = 200): void
{
    Http::fake([
        '*/identity/auth/access-token' => Http::response(['access_token' => 'tok', 'expires_in' => 3600]),
        '*/cancel' => Http::response([], $cancelStatus),
        '*' => Http::response([]),
    ]);
}

function cancelCalls(): int
{
    return Http::recorded(fn ($request) => str_ends_with($request->url(), '/cancel'))->count();
}

it('never deletes a paid order, even with its reference and session', function () {
    fakeNgenius();
    $order = unpaidOrder(['trans_state' => 'PURCHASED', 'reference' => 'DAB2026-1']);

    $this->withSession(['pending_order_ref' => 'ngenius-ref-123'])
        ->get('/order/cancel?ref=ngenius-ref-123')
        ->assertRedirect(route('home'));

    expect(Order::find($order->id))->not->toBeNull()
        ->and(cancelCalls())->toBe(0);
});

it('does not delete an unpaid order from a session that did not place it', function () {
    fakeNgenius();
    $order = unpaidOrder();

    $this->get('/order/cancel?ref=ngenius-ref-123')->assertRedirect(route('home'));

    expect(Order::find($order->id))->not->toBeNull()
        ->and(cancelCalls())->toBe(0);
});

it('does not delete an unpaid order when the session holds another reference', function () {
    fakeNgenius();
    $order = unpaidOrder();

    $this->withSession(['pending_order_ref' => 'someone-else'])
        ->get('/order/cancel?ref=ngenius-ref-123')
        ->assertRedirect(route('home'));

    expect(Order::find($order->id))->not->toBeNull();
});

it('cancels at the gateway then deletes an unpaid order from the session that placed it', function () {
    fakeNgenius();
    $order = unpaidOrder();

    $this->withSession(['pending_order_ref' => 'ngenius-ref-123'])
        ->get('/order/cancel?ref=ngenius-ref-123')
        ->assertRedirect(route('home'))
        ->assertSessionMissing('pending_order_ref');

    expect(Order::find($order->id))->toBeNull()
        ->and(cancelCalls())->toBe(1);
});

it('keeps the order when the gateway refuses the cancellation', function () {
    fakeNgenius(cancelStatus: 422);
    $order = unpaidOrder();

    $this->withSession(['pending_order_ref' => 'ngenius-ref-123'])
        ->get('/order/cancel?ref=ngenius-ref-123')
        ->assertRedirect(route('home'));

    expect(Order::find($order->id))->not->toBeNull();
});

it('answers an unknown reference like any refused one, without a 404', function () {
    fakeNgenius();

    $this->withSession(['pending_order_ref' => 'does-not-exist'])
        ->get('/order/cancel?ref=does-not-exist')
        ->assertRedirect(route('home'));
});
