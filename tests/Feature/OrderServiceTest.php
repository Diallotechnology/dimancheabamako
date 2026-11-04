<?php

declare(strict_types=1);

use App\Models\Category;
use App\Models\Client;
use App\Models\Country;
use App\Models\Order;
use App\Models\Poids;
use App\Models\Product;
use App\Models\Shipping;
use App\Models\Transport;
use App\Models\Zone;
use App\Service\CartService;
use App\Service\OrderService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

function orderFixtures(): array
{
    $zone = Zone::factory()->create();
    $country = Country::factory()->create(['zone_id' => $zone->id]);
    $transport = Transport::factory()->create();
    $poids = Poids::factory()->create();
    $shipping = Shipping::factory()->create([
        'zone_id' => $zone->id,
        'transport_id' => $transport->id,
        'poids_id' => $poids->id,
        'montant' => 15000,
    ]);
    $category = Category::factory()->create();

    return compact('country', 'transport', 'shipping', 'category');
}

function orderRequest(array $fx, array $extra = []): Request
{
    return Request::create('/order', 'POST', array_merge([
        'country_id' => $fx['country']->id,
        'transport_id' => $fx['transport']->id,
        'livraison' => $fx['shipping']->id,
        'prenom' => 'Awa',
        'nom' => 'Traoré',
        'email' => 'awa@example.com',
        'contact' => '+22370123456',
        'ville' => 'Bamako',
        'adresse' => 'ACI 2000',
    ], $extra));
}

it('creates an order, takes shipping from the database and prices lines from the cart', function () {
    $fx = orderFixtures();
    $product = Product::factory()->create(['categorie_id' => $fx['category']->id, 'prix' => 20000, 'stock' => 10]);

    $cart = app(CartService::class);
    $cart->add($product->id, $product->nom, 20000, 10, 1.5);
    $cart->update($product->id, 3);

    // Un champ "shipping" forgé par le client ne doit pas être pris en compte.
    $order = app(OrderService::class)->createOrder(orderRequest($fx, ['shipping' => 1]), $cart);

    expect($order->shipping)->toBe(15000)
        ->and($order->products)->toHaveCount(1)
        ->and((int) $order->products->first()->pivot->montant)->toBe(60000)
        ->and((int) $order->products->first()->pivot->quantity)->toBe(3)
        ->and(Client::count())->toBe(1);
});

it('reuses the same client for the same contact and email', function () {
    $fx = orderFixtures();
    $product = Product::factory()->create(['categorie_id' => $fx['category']->id, 'stock' => 10]);
    $cart = app(CartService::class);
    $cart->add($product->id, $product->nom, 1000, 10, 1);

    $service = app(OrderService::class);
    $service->createOrder(orderRequest($fx), $cart);
    $service->createOrder(orderRequest($fx), $cart);

    expect(Client::count())->toBe(1);
});

it('rejects an order when stock is insufficient and rolls everything back', function () {
    $fx = orderFixtures();
    $product = Product::factory()->create(['categorie_id' => $fx['category']->id, 'stock' => 0, 'is_preorder' => false]);

    // Panier dont la quantité (1) dépasse le stock actuel (0), comme si le stock avait baissé depuis l'ajout.
    $cart = app(CartService::class);
    $cart->add($product->id, $product->nom, 1000, 0, 1);

    expect(fn () => app(OrderService::class)->createOrder(orderRequest($fx), $cart))
        ->toThrow(Exception::class, 'Stock insuffisant');

    expect(Order::count())->toBe(0);
});

it('allows ordering beyond stock for preorder products', function () {
    $fx = orderFixtures();
    $product = Product::factory()->create(['categorie_id' => $fx['category']->id, 'stock' => 0, 'is_preorder' => true]);

    $cart = app(CartService::class);
    $cart->add($product->id, $product->nom, 1000, 0, 1, true);
    $cart->update($product->id, 4);

    $order = app(OrderService::class)->createOrder(orderRequest($fx), $cart);

    expect($order->products)->toHaveCount(1);
});

it('rejects an unknown shipping option', function () {
    $fx = orderFixtures();
    $product = Product::factory()->create(['categorie_id' => $fx['category']->id, 'stock' => 5]);
    $cart = app(CartService::class);
    $cart->add($product->id, $product->nom, 1000, 5, 1);

    expect(fn () => app(OrderService::class)->createOrder(orderRequest($fx, ['livraison' => 9999]), $cart))
        ->toThrow(ModelNotFoundException::class);
});
