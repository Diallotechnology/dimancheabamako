<?php

declare(strict_types=1);

use App\Models\Category;
use App\Models\Client;
use App\Models\Country;
use App\Models\PendingRegistration;
use App\Models\Poids;
use App\Models\Product;
use App\Models\Shipping;
use App\Models\Transport;
use App\Models\User;
use App\Models\Zone;
use App\Service\CartService;
use App\Service\ClientResolverService;
use App\Service\OrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

// A2 — un téléphone seul ne donne ni accès au Client d'un autre, ni droit de modifier ses données.

function resolverFixtures(): array
{
    $zone = Zone::factory()->create();
    $country = Country::factory()->create(['zone_id' => $zone->id]);
    $transport = Transport::factory()->create();
    $shipping = Shipping::factory()->create([
        'zone_id' => $zone->id,
        'transport_id' => $transport->id,
        'poids_id' => Poids::factory()->create()->id,
        'montant' => 15000,
    ]);

    return ['country' => $country, 'transport' => $transport, 'shipping' => $shipping, 'category' => Category::factory()->create()];
}

function resolverRequest(array $fx, array $extra): Request
{
    return Request::create('/order', 'POST', array_merge([
        'country_id' => $fx['country']->id,
        'transport_id' => $fx['transport']->id,
        'livraison' => $fx['shipping']->id,
        'prenom' => 'Pirate',
        'nom' => 'Pirate',
        'contact' => '+22370123456',
        'ville' => 'Bamako',
        'adresse' => 'ACI 2000',
    ], $extra));
}

function victimClient(): Client
{
    return Client::factory()->create([
        'prenom' => 'Victime',
        'nom' => 'Réelle',
        'pays' => 'Mali',
        'email' => 'victime@example.com',
        'contact' => '+22370123456',
    ]);
}

function cartWithOneProduct(array $fx): CartService
{
    $product = Product::factory()->create(['categorie_id' => $fx['category']->id, 'prix' => 1000, 'poids' => 1, 'stock' => 10]);
    $cart = app(CartService::class);
    $cart->add($product->id, $product->nom, 1000, 10, 1);

    return $cart;
}

it('does not overwrite a client matched by phone only', function () {
    $victim = victimClient();

    $result = app(ClientResolverService::class)->resolve([
        'prenom' => 'Pirate', 'nom' => 'Pirate', 'pays' => 'France',
        'contact' => '+22370123456', 'email' => 'attaquant@example.com',
    ]);

    expect($result->id)->toBe($victim->id)
        ->and($victim->fresh()->only('prenom', 'nom', 'pays', 'email'))->toBe([
            'prenom' => 'Victime', 'nom' => 'Réelle', 'pays' => 'Mali', 'email' => 'victime@example.com',
        ]);
});

it('still updates the client when phone and email both match', function () {
    $victim = victimClient();

    app(ClientResolverService::class)->resolve([
        'prenom' => 'Nouveau', 'nom' => 'Nom', 'pays' => 'Sénégal',
        'contact' => '+22370123456', 'email' => 'victime@example.com',
    ]);

    expect($victim->fresh()->only('prenom', 'nom', 'pays'))->toBe(['prenom' => 'Nouveau', 'nom' => 'Nom', 'pays' => 'Sénégal']);
});

it('does not create or link an account at checkout when only the phone matches', function () {
    $fx = resolverFixtures();
    $victim = victimClient();

    $order = app(OrderService::class)->createOrder(
        resolverRequest($fx, ['email' => 'attaquant@example.com', 'password' => 'Str0ng-Passw0rd!']),
        cartWithOneProduct($fx),
    );

    // La commande invité reste possible, rattachée au Client existant, sans toucher à ses données ni créer de compte.
    expect($order->client_id)->toBe($victim->id)
        ->and(User::where('email', 'attaquant@example.com')->exists())->toBeFalse()
        ->and($victim->fresh()->prenom)->toBe('Victime');
});

it('does not link an existing account without a client when only the phone matches', function () {
    $fx = resolverFixtures();
    $victim = victimClient();
    $existing = User::factory()->create(['email' => 'attaquant@example.com', 'client_id' => null]);

    app(OrderService::class)->createOrder(
        resolverRequest($fx, ['email' => 'attaquant@example.com', 'password' => 'Str0ng-Passw0rd!']),
        cartWithOneProduct($fx),
    );

    expect($existing->fresh()->client_id)->toBeNull()
        ->and($victim->fresh()->prenom)->toBe('Victime');
});

it('still opens a pending registration at checkout, linked to the client once confirmed, when the email matches', function () {
    Mail::fake();
    $fx = resolverFixtures();
    $victim = victimClient();

    app(OrderService::class)->createOrder(
        resolverRequest($fx, ['email' => 'victime@example.com', 'password' => 'Str0ng-Passw0rd!']),
        cartWithOneProduct($fx),
    );

    $this->get('/confirm-registration/'.PendingRegistration::where('email', 'victime@example.com')->value('token'));

    expect(User::where('email', 'victime@example.com')->value('client_id'))->toBe($victim->id);
});

it('refuses to confirm a registration whose phone belongs to another client', function () {
    $victim = victimClient();
    PendingRegistration::create([
        'prenom' => 'Pirate', 'nom' => 'Pirate', 'pays' => 'France',
        'contact' => '+22370123456', 'email' => 'attaquant@example.com',
        'role' => 'Client', 'password' => Hash::make('Str0ng-Passw0rd!'),
        'token' => str_repeat('a', 64), 'expires_at' => now()->addHour(),
    ]);

    $this->get('/confirm-registration/'.str_repeat('a', 64));

    expect(User::where('email', 'attaquant@example.com')->exists())->toBeFalse()
        ->and(Auth::check())->toBeFalse()
        ->and($victim->fresh()->only('prenom', 'nom', 'pays'))->toBe(['prenom' => 'Victime', 'nom' => 'Réelle', 'pays' => 'Mali']);
});
