<?php

declare(strict_types=1);

use App\Http\Requests\StoreOrderRequest;
use App\Mail\ConfirmRegistrationMail;
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
use App\Service\OrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

// A1 — le checkout ne crée jamais de compte : un mot de passe saisi ouvre une inscription en attente
// confirmée par un lien envoyé à l'e-mail (preuve de possession). La commande n'est jamais bloquée.

function checkoutFixtures(): array
{
    $zone = Zone::factory()->create();
    $shipping = Shipping::factory()->create([
        'zone_id' => $zone->id,
        'transport_id' => ($transport = Transport::factory()->create())->id,
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

/** @return array<string, mixed> */
function checkoutData(array $fx, array $extra = []): array
{
    return array_merge([
        'country_id' => $fx['country']->id,
        'transport_id' => $fx['transport']->id,
        'livraison' => $fx['shipping']->id,
        'prenom' => 'Awa',
        'nom' => 'Traoré',
        'email' => 'awa@example.com',
        'contact' => '+22370123456',
        'ville' => 'Bamako',
        'adresse' => 'ACI 2000',
    ], $extra);
}

function checkout(array $fx, array $extra = [])
{
    $product = Product::factory()->create(['categorie_id' => $fx['category']->id, 'prix' => 1000, 'poids' => 1, 'stock' => 10]);
    $cart = app(CartService::class);
    $cart->add($product->id, $product->nom, 1000, 10, 1);

    return app(OrderService::class)->createOrder(Request::create('/order', 'POST', checkoutData($fx, $extra)), $cart);
}

it('creates no account at checkout but a pending registration confirmed by e-mail', function () {
    Mail::fake();
    $fx = checkoutFixtures();

    $order = checkout($fx, ['password' => 'Str0ng-Passw0rd!']);

    $pending = PendingRegistration::where('email', 'awa@example.com')->first();

    expect($order->client_id)->not->toBeNull()
        ->and(User::where('email', 'awa@example.com')->exists())->toBeFalse()
        ->and($pending)->not->toBeNull()
        ->and(Hash::check('Str0ng-Passw0rd!', $pending->password))->toBeTrue()
        ->and($pending->expires_at->isFuture())->toBeTrue();

    Mail::assertSent(ConfirmRegistrationMail::class, fn ($mail) => PendingRegistration::hashToken($mail->token) === $pending->token);
});

it('links the account to the order client once the e-mail link is confirmed', function () {
    Mail::fake();
    $fx = checkoutFixtures();
    $order = checkout($fx, ['password' => 'Str0ng-Passw0rd!']);
    $token = sentConfirmationToken();

    expect($token)->not->toBeEmpty()
        ->and(User::where('email', 'awa@example.com')->exists())->toBeFalse();

    $this->post('/confirm-registration/'.$token);

    expect(User::where('email', 'awa@example.com')->value('client_id'))->toBe($order->client_id);
});

it('creates nothing without a password', function () {
    Mail::fake();

    checkout(checkoutFixtures());

    expect(PendingRegistration::count())->toBe(0)
        ->and(User::count())->toBe(0);
    Mail::assertNothingSent();
});

it('does not touch an existing account when someone checks out with its e-mail', function () {
    Mail::fake();
    $user = User::factory()->create(['email' => 'awa@example.com', 'password' => 'Original-Passw0rd!']);
    $hash = $user->password;

    checkout(checkoutFixtures(), ['password' => 'Attacker-Passw0rd!']);

    expect($user->fresh()->password)->toBe($hash)
        ->and(PendingRegistration::count())->toBe(0);
    Mail::assertNothingSent();
});

it('does not let a second checkout replace the password of a pending registration', function () {
    Mail::fake();
    $fx = checkoutFixtures();
    checkout($fx, ['password' => 'Victim-Passw0rd!1']);
    $before = PendingRegistration::where('email', 'awa@example.com')->first();

    checkout($fx, ['password' => 'Attacker-Passw0rd!1']);

    $after = PendingRegistration::where('email', 'awa@example.com')->first();
    expect(PendingRegistration::count())->toBe(1)
        ->and(Hash::check('Victim-Passw0rd!1', $after->password))->toBeTrue()
        ->and($after->id)->toBe($before->id);
});

it('still places the order when the phone is already held by another pending registration', function () {
    Mail::fake();
    PendingRegistration::create([
        'prenom' => 'Autre', 'nom' => 'Personne', 'pays' => 'Mali', 'contact' => '+22370123456',
        'email' => 'autre@example.com', 'role' => 'Client', 'password' => Hash::make('x'),
        'token' => PendingRegistration::hashToken(str_repeat('b', 64)), 'expires_at' => now()->addHour(),
    ]);

    $order = checkout(checkoutFixtures(), ['password' => 'Str0ng-Passw0rd!']);

    expect($order->exists)->toBeTrue()
        ->and(PendingRegistration::count())->toBe(1)
        ->and(Client::count())->toBe(1);
});

it('rejects a disposable e-mail when a password is given at checkout', function () {
    $fx = checkoutFixtures();
    $data = checkoutData($fx, ['email' => 'jetable@yopmail.com', 'password' => 'Str0ng-Passw0rd!']);

    $request = StoreOrderRequest::create('/order', 'POST', $data);

    expect(Validator::make($data, $request->rules())->errors()->has('email'))->toBeTrue();
});
