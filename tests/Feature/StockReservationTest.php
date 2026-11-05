<?php

declare(strict_types=1);

use App\Jobs\OrderMailJob;
use App\Models\Category;
use App\Models\Client;
use App\Models\Country;
use App\Models\Order;
use App\Models\Poids;
use App\Models\Product;
use App\Models\Shipping;
use App\Models\Transport;
use App\Models\Zone;
use App\Notifications\NotifOrderStockIssue;
use App\Service\CartService;
use App\Service\OrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;

// Réservation de stock : décrément à la commande, restitution à l'annulation / l'expiration.

function reservationFixtures(): array
{
    $zone = Zone::factory()->create();
    $transport = Transport::factory()->create();
    $shipping = Shipping::factory()->create([
        'zone_id' => $zone->id,
        'transport_id' => $transport->id,
        'poids_id' => Poids::factory()->create()->id,
        'montant' => 15000,
    ]);

    return [
        'country' => Country::factory()->create(['zone_id' => $zone->id]),
        'transport' => $transport,
        'shipping' => $shipping,
        'category' => Category::factory()->create(),
    ];
}

function reservationRequest(array $fx): Request
{
    return Request::create('/order', 'POST', [
        'country_id' => $fx['country']->id,
        'transport_id' => $fx['transport']->id,
        'livraison' => $fx['shipping']->id,
        'prenom' => 'Awa',
        'nom' => 'Traoré',
        'email' => 'awa@example.com',
        'contact' => '+22370123456',
        'ville' => 'Bamako',
        'adresse' => 'ACI 2000',
    ]);
}

/** Commande impayée portant `$quantity` unités de `$product`, stock déjà réservé en base. */
function reservedOrder(Product $product, int $quantity, array $overrides = []): Order
{
    $order = Order::factory()->create(array_merge([
        'client_id' => Client::factory()->create()->id,
        'country_id' => Country::factory()->create(['zone_id' => Zone::factory()->create()->id])->id,
        'transport_id' => Transport::factory()->create()->id,
        'trans_ref' => 'ngenius-ref-'.uniqid(),
        'reference' => null,
        'trans_state' => null,
    ], $overrides));

    $order->products()->attach($product->id, ['quantity' => $quantity, 'montant' => $product->prix * $quantity]);

    return $order;
}

function fakeGatewayState(?string $state, int $statusCode = 200): void
{
    $body = $state === null ? ['_embedded' => []] : ['_embedded' => ['payment' => [['state' => $state]]]];

    Http::fake([
        '*/identity/auth/access-token' => Http::response(['access_token' => 'tok', 'expires_in' => 3600]),
        '*/cancel' => Http::response([]),
        '*' => Http::response($body, $statusCode),
    ]);
}

// 1. createOrder décrémente

it('reserves stock when the order is created', function () {
    $fx = reservationFixtures();
    $product = Product::factory()->create(['categorie_id' => $fx['category']->id, 'prix' => 1000, 'poids' => 1, 'stock' => 10, 'is_preorder' => false]);

    $cart = app(CartService::class);
    $cart->add($product->id, $product->nom, 1000, 10, 1);
    $cart->update($product->id, 3);

    app(OrderService::class)->createOrder(reservationRequest($fx), $cart);

    expect($product->fresh()->stock)->toBe(7);
});

it('does not touch the stock of preorder products', function () {
    $fx = reservationFixtures();
    $product = Product::factory()->create(['categorie_id' => $fx['category']->id, 'prix' => 1000, 'poids' => 1, 'stock' => 0, 'is_preorder' => true]);

    $cart = app(CartService::class);
    $cart->add($product->id, $product->nom, 1000, 0, 1, true);
    $cart->update($product->id, 2);

    app(OrderService::class)->createOrder(reservationRequest($fx), $cart);

    expect($product->fresh()->stock)->toBe(0);
});

// 2. CheckPayment : restitution sur échec, pas de double décrément sur succès

it('restores the stock when check-payment sees a failed, cancelled or expired payment', function (string $state) {
    $product = reservationProduct(['stock' => 7, 'prix' => 1000, 'is_preorder' => false]);
    $order = reservedOrder($product, 3);
    fakeGatewayState($state);

    $this->artisan('app:check-payment')->assertSuccessful();

    expect(Order::find($order->id))->toBeNull()
        ->and($product->fresh()->stock)->toBe(10);
})->with(['FAILED', 'CANCELLED', 'EXPIRED']);

it('does not decrement the stock a second time on PURCHASED', function () {
    Bus::fake();
    Notification::fake();
    $product = reservationProduct(['stock' => 7, 'prix' => 1000, 'is_preorder' => false]);
    $order = reservedOrder($product, 3);
    fakeGatewayState('PURCHASED');

    $this->artisan('app:check-payment')->assertSuccessful();

    expect($product->fresh()->stock)->toBe(7)
        ->and($order->fresh()->reference)->not->toBeNull();
    Bus::assertDispatched(OrderMailJob::class);
    Notification::assertNothingSent();
});

it('accepts a PURCHASED order that reserved the last unit (stock 0)', function () {
    Bus::fake();
    Notification::fake();
    $product = reservationProduct(['stock' => 0, 'prix' => 1000, 'is_preorder' => false]);
    $order = reservedOrder($product, 1);
    fakeGatewayState('PURCHASED');

    $this->artisan('app:check-payment')->assertSuccessful();

    expect($order->fresh()->etat)->not->toBe('Stock épuisé')
        ->and($order->fresh()->reference)->not->toBeNull();
    Notification::assertNothingSent();
});

it('flags an oversell (negative stock) on PURCHASED as a safety net', function () {
    Bus::fake();
    Notification::fake();
    $product = reservationProduct(['stock' => -1, 'prix' => 1000, 'is_preorder' => false]);
    $order = reservedOrder($product, 1);
    fakeGatewayState('PURCHASED');

    $this->artisan('app:check-payment')->assertSuccessful();

    expect($order->fresh()->etat)->toBe('Stock épuisé');
    Notification::assertSentOnDemand(NotifOrderStockIssue::class);
});

// 3. OrderController::cancel restitue après le succès de cancelPaymentLink

it('restores the stock when the customer cancels at the gateway', function () {
    fakeGatewayState('CREATED');
    $product = reservationProduct(['stock' => 7, 'prix' => 1000, 'is_preorder' => false]);
    $order = reservedOrder($product, 3, ['trans_ref' => 'ngenius-ref-123']);

    $this->withSession(['pending_order_ref' => 'ngenius-ref-123'])
        ->get('/order/cancel?ref=ngenius-ref-123')
        ->assertRedirect(route('home'));

    expect(Order::find($order->id))->toBeNull()
        ->and($product->fresh()->stock)->toBe(10);
});

it('keeps the reservation when the gateway refuses the cancellation', function () {
    Http::fake([
        '*/identity/auth/access-token' => Http::response(['access_token' => 'tok', 'expires_in' => 3600]),
        '*/cancel' => Http::response([], 422),
        '*' => Http::response([]),
    ]);
    $product = reservationProduct(['stock' => 7, 'prix' => 1000, 'is_preorder' => false]);
    $order = reservedOrder($product, 3, ['trans_ref' => 'ngenius-ref-123']);

    $this->withSession(['pending_order_ref' => 'ngenius-ref-123'])
        ->get('/order/cancel?ref=ngenius-ref-123');

    expect(Order::find($order->id))->not->toBeNull()
        ->and($product->fresh()->stock)->toBe(7);
});

// 4. DeleteOrderPaymentExpire vérifie N-Genius avant de libérer

it('restores the stock and deletes an abandoned order (gateway has no payment state)', function () {
    fakeGatewayState(null);
    $product = reservationProduct(['stock' => 7, 'prix' => 1000, 'is_preorder' => false]);
    $order = reservedOrder($product, 3, ['created_at' => now()->subMinutes(15)]);

    $this->artisan('app:delete-order-payment-expire')->assertSuccessful();

    expect(Order::find($order->id))->toBeNull()
        ->and($product->fresh()->stock)->toBe(10);
});

it('restores the stock and deletes an expired order when the gateway says it failed', function (string $state) {
    fakeGatewayState($state);
    $product = reservationProduct(['stock' => 7, 'prix' => 1000, 'is_preorder' => false]);
    $order = reservedOrder($product, 3, ['created_at' => now()->subMinutes(15)]);

    $this->artisan('app:delete-order-payment-expire')->assertSuccessful();

    expect(Order::find($order->id))->toBeNull()
        ->and($product->fresh()->stock)->toBe(10);
})->with(['FAILED', 'CANCELLED', 'EXPIRED']);

it('neither deletes nor restores an order the gateway reports as PURCHASED', function () {
    fakeGatewayState('PURCHASED');
    $product = reservationProduct(['stock' => 7, 'prix' => 1000, 'is_preorder' => false]);
    $order = reservedOrder($product, 3, ['created_at' => now()->subMinutes(15)]);

    $this->artisan('app:delete-order-payment-expire')->assertSuccessful();

    expect(Order::find($order->id))->not->toBeNull()
        ->and($product->fresh()->stock)->toBe(7);
});

it('touches nothing when the gateway call fails, and keeps processing the batch', function () {
    Http::fake([
        '*/identity/auth/access-token' => Http::response(['access_token' => 'tok', 'expires_in' => 3600]),
        '*' => Http::response(['error' => 'boom'], 500),
    ]);
    $product = reservationProduct(['stock' => 7, 'prix' => 1000, 'is_preorder' => false]);
    $order = reservedOrder($product, 3, ['created_at' => now()->subMinutes(15)]);

    $this->artisan('app:delete-order-payment-expire')->assertSuccessful();

    expect(Order::find($order->id))->not->toBeNull()
        ->and($product->fresh()->stock)->toBe(7);
});

it('ignores preorder lines when restoring stock', function () {
    fakeGatewayState('FAILED');
    $product = reservationProduct(['stock' => 0, 'prix' => 1000, 'is_preorder' => true]);
    $order = reservedOrder($product, 2);

    $this->artisan('app:check-payment')->assertSuccessful();

    expect(Order::find($order->id))->toBeNull()
        ->and($product->fresh()->stock)->toBe(0);
});

function reservationProduct(array $attributes): Product
{
    return Product::factory()->create(array_merge(['categorie_id' => Category::factory()->create()->id], $attributes));
}
