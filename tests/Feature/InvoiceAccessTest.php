<?php

declare(strict_types=1);

use App\Mail\OrderMail;
use App\Models\Category;
use App\Models\Client;
use App\Models\Country;
use App\Models\Order;
use App\Models\Product;
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

it('styles the invoice with its own stylesheet, not the retired Evara theme', function () {
    $order = purchasedOrder();

    $this->get(URL::signedRoute('order.invoice.signed', ['id' => $order->trans_ref]))
        ->assertOk()
        ->assertSee('css/invoice.css', false)
        ->assertDontSee('admin/assets', false);
    expect(public_path('css/invoice.css'))->toBeFile();
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

// Montants de la facture : chaque ligne affiche son propre montant, une seule devise, via PriceService.

function invoiceWithLines(int $shipping, array $lineAmounts): string
{
    $order = purchasedOrder();
    $order->update(['shipping' => (string) $shipping]);

    foreach ($lineAmounts as $montant) {
        $order->products()->attach(
            Product::factory()->create(['categorie_id' => Category::factory()->create()->id])->id,
            ['quantity' => 1, 'montant' => $montant],
        );
    }

    return URL::signedRoute('order.invoice.signed', ['id' => $order->trans_ref]);
}

it('shows each line with its own amount, the subtotal, the shipping and the total in CFA', function () {
    session(['devise' => 'CFA', 'taux_eur' => 500]);

    $this->get(invoiceWithLines(15000, [12000, 8000]))
        ->assertOk()
        ->assertSee('12 000 CFA')   // ligne 1 : pas le montant de toute la commande
        ->assertSee('8 000 CFA')    // ligne 2
        ->assertSee('20 000 CFA')   // sous-total
        ->assertSee('15 000 CFA')   // livraison
        ->assertSee('35 000 CFA');  // total
});

it('shows each amount once in euros, without a doubled currency sign', function () {
    session(['devise' => 'EUR', 'taux_eur' => 500]);

    $this->get(invoiceWithLines(50000, [500000, 250000]))
        ->assertOk()
        ->assertSee('1 000,00 €')   // ligne 1
        ->assertSee('500,00 €')     // ligne 2
        ->assertSee('1 500,00 €')   // sous-total
        ->assertSee('100,00 €')     // livraison
        ->assertSee('1 600,00 €')   // total : au-delà de 1 000 €, le séparateur de milliers ne doit pas fausser la somme
        ->assertDontSee('€ €');
});
