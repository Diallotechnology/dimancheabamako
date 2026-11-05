<?php

declare(strict_types=1);

use App\Exceptions\StaleCartException;
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
use DomainException;
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
    $product = Product::factory()->create(['categorie_id' => $fx['category']->id, 'prix' => 20000, 'stock' => 10, 'poids' => 1.5]);

    $cart = app(CartService::class);
    $cart->add($product->id, $product->nom, 20000, 10, 1.5);
    $cart->update($product->id, 3);

    // Un champ "shipping" forgé par le client ne doit pas être pris en compte.
    $order = app(OrderService::class)->createOrder(orderRequest($fx, ['shipping' => 1]), $cart);

    expect($order->shipping)->toBe(15000)
        ->and($order->poids)->toBe('4.5 Kg')
        ->and($order->products)->toHaveCount(1)
        ->and((int) $order->products->first()->pivot->montant)->toBe(60000)
        ->and((int) $order->products->first()->pivot->quantity)->toBe(3)
        ->and(Client::count())->toBe(1);
});

it('reuses the same client for the same contact and email', function () {
    $fx = orderFixtures();
    $product = Product::factory()->create(['categorie_id' => $fx['category']->id, 'prix' => 1000, 'poids' => 1, 'stock' => 10]);
    $cart = app(CartService::class);
    $cart->add($product->id, $product->nom, 1000, 10, 1);

    $service = app(OrderService::class);
    $service->createOrder(orderRequest($fx), $cart);
    $service->createOrder(orderRequest($fx), $cart);

    expect(Client::count())->toBe(1);
});

it('rejects an order when stock is insufficient and rolls everything back', function () {
    $fx = orderFixtures();
    $product = Product::factory()->create(['categorie_id' => $fx['category']->id, 'prix' => 1000, 'poids' => 1, 'stock' => 0, 'is_preorder' => false]);

    // Panier dont la quantité (1) dépasse le stock actuel (0), comme si le stock avait baissé depuis l'ajout.
    $cart = app(CartService::class);
    $cart->add($product->id, $product->nom, 1000, 0, 1);

    expect(fn () => app(OrderService::class)->createOrder(orderRequest($fx), $cart))
        ->toThrow(StaleCartException::class, 'Stock insuffisant');

    expect(Order::count())->toBe(0);
});

it('allows ordering beyond stock for preorder products', function () {
    $fx = orderFixtures();
    $product = Product::factory()->create(['categorie_id' => $fx['category']->id, 'prix' => 1000, 'poids' => 1, 'stock' => 0, 'is_preorder' => true]);

    $cart = app(CartService::class);
    $cart->add($product->id, $product->nom, 1000, 0, 1, true);
    $cart->update($product->id, 4);

    $order = app(OrderService::class)->createOrder(orderRequest($fx), $cart);

    expect($order->products)->toHaveCount(1);
});

function cartWithWeight(array $fx, float $poids, int $quantity = 1): CartService
{
    $product = Product::factory()->create(['categorie_id' => $fx['category']->id, 'prix' => 1000, 'poids' => $poids, 'stock' => 20]);
    $cart = app(CartService::class);
    $cart->add($product->id, $product->nom, 1000, 20, $poids);
    $cart->update($product->id, $quantity);

    return $cart;
}

// I1 — le tarif de livraison est recalculé côté serveur, jamais choisi par le client

it('rejects a forged shipping id from another zone and transport', function () {
    $fx = orderFixtures();
    $cheap = Shipping::factory()->create([
        'zone_id' => Zone::factory()->create()->id,
        'transport_id' => Transport::factory()->create()->id,
        'poids_id' => Poids::factory()->create()->id,
        'montant' => 1,
    ]);
    $cart = cartWithWeight($fx, 1.5, 3);

    expect(fn () => app(OrderService::class)->createOrder(orderRequest($fx, ['livraison' => $cheap->id]), $cart))
        ->toThrow(DomainException::class);

    expect(Order::count())->toBe(0);
});

it('rejects a forged shipping id from another weight bracket of the same zone and transport', function () {
    $fx = orderFixtures();
    $cheap = Shipping::factory()->create([
        'zone_id' => $fx['shipping']->zone_id,
        'transport_id' => $fx['shipping']->transport_id,
        'poids_id' => Poids::factory()->create(['min' => 10, 'max' => 20])->id,
        'montant' => 1,
    ]);
    $cart = cartWithWeight($fx, 1.5, 3); // 4,5 kg → tranche 0-5 kg (15 000)

    expect(fn () => app(OrderService::class)->createOrder(orderRequest($fx, ['livraison' => $cheap->id]), $cart))
        ->toThrow(DomainException::class);

    expect(Order::count())->toBe(0);
});

it('uses the weight bracket matching the real cart weight', function () {
    $fx = orderFixtures();
    $heavy = Shipping::factory()->create([
        'zone_id' => $fx['shipping']->zone_id,
        'transport_id' => $fx['shipping']->transport_id,
        'poids_id' => Poids::factory()->create(['min' => 5.01, 'max' => 20])->id,
        'montant' => 40000,
    ]);
    $cart = cartWithWeight($fx, 4, 2); // 8 kg → tranche 5,01-20 kg

    $order = app(OrderService::class)->createOrder(orderRequest($fx, ['livraison' => $heavy->id]), $cart);

    expect($order->shipping)->toBe(40000);
});

it('rejects an order when no shipping bracket matches the cart weight', function () {
    $fx = orderFixtures();
    $cart = cartWithWeight($fx, 4, 3); // 12 kg, seule tranche existante : 0-5 kg

    expect(fn () => app(OrderService::class)->createOrder(orderRequest($fx), $cart))
        ->toThrow(DomainException::class);

    expect(Order::count())->toBe(0);
});

it('rejects an unknown shipping option', function () {
    $fx = orderFixtures();
    $cart = cartWithWeight($fx, 1);

    expect(fn () => app(OrderService::class)->createOrder(orderRequest($fx, ['livraison' => 9999]), $cart))
        ->toThrow(DomainException::class);
});

// M2 — prix et stock du panier (cache) revalidés avec la base à la commande

it('refuses an order when the product price changed since it was added to the cart', function () {
    $fx = orderFixtures();
    $product = Product::factory()->create(['categorie_id' => $fx['category']->id, 'prix' => 20000, 'stock' => 10]);

    $cart = app(CartService::class);
    $cart->add($product->id, $product->nom, 20000, 10, 1.0);

    $product->update(['prix' => 25000]);

    expect(fn () => app(OrderService::class)->createOrder(orderRequest($fx), $cart))
        ->toThrow(StaleCartException::class);

    // Aucune commande créée, et le panier affiche désormais le prix réel.
    expect(Order::count())->toBe(0)
        ->and((int) $cart->get($product->id)['price'])->toBe(25000)
        ->and((int) $cart->getTotal())->toBe(25000);
});

it('refuses an order when the promotion applied in the cart has expired', function () {
    $fx = orderFixtures();
    $product = Product::factory()->create(['categorie_id' => $fx['category']->id, 'prix' => 20000, 'stock' => 10]);

    // Le panier a été rempli à -50 % ; aucune promotion active n'existe plus en base.
    $cart = app(CartService::class);
    $cart->add($product->id, $product->nom, 10000, 10, 1.0);

    expect(fn () => app(OrderService::class)->createOrder(orderRequest($fx), $cart))
        ->toThrow(StaleCartException::class);

    expect(Order::count())->toBe(0)
        ->and((int) $cart->getTotal())->toBe(20000);
});

it('refuses an order when stock dropped below the cart quantity', function () {
    $fx = orderFixtures();
    $product = Product::factory()->create(['categorie_id' => $fx['category']->id, 'prix' => 20000, 'stock' => 10, 'is_preorder' => false]);

    $cart = app(CartService::class);
    $cart->add($product->id, $product->nom, 20000, 10, 1.0);
    $cart->update($product->id, 5);

    $product->update(['stock' => 2]);

    expect(fn () => app(OrderService::class)->createOrder(orderRequest($fx), $cart))
        ->toThrow(StaleCartException::class, 'Stock insuffisant');

    expect(Order::count())->toBe(0);
});

it('refuses an order when a product of the cart no longer exists', function () {
    $fx = orderFixtures();
    $product = Product::factory()->create(['categorie_id' => $fx['category']->id, 'prix' => 20000, 'stock' => 10]);

    $cart = app(CartService::class);
    $cart->add($product->id, $product->nom, 20000, 10, 1.0);

    $product->delete();

    expect(fn () => app(OrderService::class)->createOrder(orderRequest($fx), $cart))
        ->toThrow(StaleCartException::class);

    expect(Order::count())->toBe(0)
        ->and($cart->getContent())->toBeEmpty();
});

it('computes the shipping weight from the database, not from the cached weight', function () {
    $fx = orderFixtures();
    $product = Product::factory()->create(['categorie_id' => $fx['category']->id, 'prix' => 20000, 'stock' => 10, 'poids' => 4.0]);

    // Poids périmé dans le panier (1 kg) alors que le produit pèse maintenant 4 kg.
    $cart = app(CartService::class);
    $cart->add($product->id, $product->nom, 20000, 10, 1.0);

    try {
        app(OrderService::class)->createOrder(orderRequest($fx), $cart);
    } catch (DomainException) {
        // Le poids seul n'invalide pas le panier ; on vérifie juste qu'il est resynchronisé.
    }

    expect($cart->getTotalWeight())->toBe(4.0);
});
