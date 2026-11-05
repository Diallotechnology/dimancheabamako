<?php

declare(strict_types=1);

use App\Mail\OrderMail;
use App\Models\Client;
use App\Models\Country;
use App\Models\Order;
use App\Models\Transport;
use App\Models\Zone;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

function purchasedOrder(string $createdAt = '2026-11-10 10:00:00'): Order
{
    $country = Country::factory()->create(['zone_id' => Zone::factory()->create()->id]);

    return Order::factory()->create([
        'client_id' => Client::factory()->create()->id,
        'country_id' => $country->id,
        'transport_id' => Transport::factory()->create()->id,
        'trans_ref' => (string) Str::uuid(),
        'trans_state' => 'PURCHASED',
        'created_at' => $createdAt,
    ]);
}

beforeEach(function () {
    // Date de bascule : les commandes créées avant elle gardent leur ancien lien non signé.
    config(['invoice.legacy_cutoff' => '2026-11-06']);
});

// M6 — la facture contient des données personnelles : l'UUID N-Genius seul ne doit plus suffire

it('serves the invoice through a valid signed link', function () {
    $order = purchasedOrder();

    $this->get(URL::signedRoute('order.invoice.signed', ['id' => $order->trans_ref]))
        ->assertOk()
        ->assertSee($order->reference);
});

it('refuses the signed invoice route without a signature', function () {
    $order = purchasedOrder();

    $this->get(route('order.invoice.signed', ['id' => $order->trans_ref]))->assertForbidden();
});

it('refuses a tampered or reused signature', function () {
    $order = purchasedOrder();
    $other = purchasedOrder();

    $url = URL::signedRoute('order.invoice.signed', ['id' => $order->trans_ref]);

    $this->get($url.'x')->assertForbidden();
    $this->get(str_replace($order->trans_ref, $other->trans_ref, $url))->assertForbidden();
});

it('does not serve the unsigned legacy link for orders created after the cutoff', function () {
    $order = purchasedOrder('2026-11-10 10:00:00');

    $this->get(route('order.invoice', $order->trans_ref))->assertNotFound();
});

it('still serves the unsigned legacy link for orders created before the cutoff', function () {
    $order = purchasedOrder('2026-11-01 10:00:00');

    $this->get(route('order.invoice', $order->trans_ref))->assertOk();
});

it('puts a signed invoice link in the order e-mail', function () {
    $order = purchasedOrder();

    (new OrderMail($order))
        ->assertSeeInHtml('signature=')
        ->assertSeeInHtml($order->trans_ref);
});
