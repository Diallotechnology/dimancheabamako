<?php

declare(strict_types=1);

use App\Livewire\Panier;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Livewire;

// I4 — le login du panier applique les mêmes protections que le login principal

beforeEach(function () {
    session(['devise' => 'CFA', 'taux_eur' => 655.957]);
});

function panierLogin(string $email, string $password)
{
    return Livewire::test(Panier::class)
        ->set('email', $email)
        ->set('password', $password)
        ->call('login_submit');
}

it('locks the cart login after 5 failed attempts, even with the right password', function () {
    $user = User::factory()->create(['password' => 'Right-Passw0rd!']);

    foreach (range(1, 5) as $_) {
        panierLogin($user->email, 'wrong-password');
    }

    panierLogin($user->email, 'Right-Passw0rd!')->assertHasErrors('email');

    expect(Auth::check())->toBeFalse();
});

it('shares the failure counter with the main login form', function () {
    $user = User::factory()->create(['password' => 'Right-Passw0rd!']);

    foreach (range(1, 5) as $_) {
        $this->post(route('login'), ['email' => $user->email, 'password' => 'wrong-password']);
    }

    panierLogin($user->email, 'Right-Passw0rd!')->assertHasErrors('email');

    expect(Auth::check())->toBeFalse();
});

it('does not log in an account that still has to change its initial password', function () {
    $user = User::factory()->create(['password' => 'Initial-Passw0rd!', 'change_password' => false]);

    $component = panierLogin($user->email, 'Initial-Passw0rd!');

    expect(Auth::check())->toBeFalse();
    $component->assertRedirectContains('signature=');
});

it('keeps regenerating the session id on a successful cart login', function () {
    $user = User::factory()->create(['password' => 'Right-Passw0rd!']);
    $before = session()->getId();

    panierLogin($user->email, 'Right-Passw0rd!');

    expect(Auth::check())->toBeTrue()
        ->and(session()->getId())->not->toBe($before);
});

it('still logs in with valid credentials and keeps the guest cart', function () {
    $category = App\Models\Category::factory()->create();
    $product = App\Models\Product::factory()->create(['categorie_id' => $category->id, 'stock' => 5]);
    $user = User::factory()->create(['password' => 'Right-Passw0rd!']);

    $cart = app(App\Service\CartService::class);
    $cart->add($product->id, $product->nom, (int) $product->prix, 5, 1.0);

    panierLogin($user->email, 'Right-Passw0rd!')->assertRedirect(route('panier'));

    expect(Auth::id())->toBe($user->id)
        ->and($cart->setUser($user->id)->has($product->id))->toBeTrue();
});
