<?php

declare(strict_types=1);

use App\Enum\RoleEnum;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\URL;

// C1 — POST password/change : jeton signé obligatoire + change_password === false

it('rejects a password change without a signed token', function () {
    $victim = User::factory()->create([
        'role' => RoleEnum::ADMIN,
        'change_password' => true,
    ]);
    $oldHash = $victim->password;

    $this->post(route('change_password'), [
        'email' => $victim->email,
        'password' => 'N3w-Str0ng-Passw0rd!',
        'password_confirmation' => 'N3w-Str0ng-Passw0rd!',
    ])->assertForbidden();

    expect($victim->fresh()->password)->toBe($oldHash);
});

it('rejects the change form without a signed token', function () {
    $this->get(route('change.password', ['email' => 'a@b.test']))->assertForbidden();
});

it('refuses a signed change when the account already changed its password', function () {
    $user = User::factory()->create(['change_password' => true]);
    $oldHash = $user->password;

    $url = URL::temporarySignedRoute('change_password', now()->addMinutes(30), ['email' => $user->email]);

    $this->post($url, [
        'password' => 'N3w-Str0ng-Passw0rd!',
        'password_confirmation' => 'N3w-Str0ng-Passw0rd!',
    ])->assertForbidden();

    expect($user->fresh()->password)->toBe($oldHash);
});

it('does not let a signed token for one email change another account', function () {
    $mine = User::factory()->create(['change_password' => false]);
    $victim = User::factory()->create(['change_password' => true]);
    $oldHash = $victim->password;

    $url = URL::temporarySignedRoute('change_password', now()->addMinutes(30), ['email' => $mine->email]);

    $this->post($url, [
        'email' => $victim->email,
        'password' => 'N3w-Str0ng-Passw0rd!',
        'password_confirmation' => 'N3w-Str0ng-Passw0rd!',
    ]);

    expect($victim->fresh()->password)->toBe($oldHash);
});

it('sends a first-login user to a signed change page, then lets them set a password', function () {
    $user = User::factory()->create([
        'change_password' => false,
        'password' => 'Initial-Passw0rd!',
    ]);

    $response = $this->post(route('login'), ['email' => $user->email, 'password' => 'Initial-Passw0rd!']);

    $response->assertRedirect();
    $changeUrl = $response->headers->get('Location');
    expect($changeUrl)->toContain('signature=');

    $this->get($changeUrl)->assertOk();

    $post = URL::temporarySignedRoute('change_password', now()->addMinutes(30), ['email' => $user->email]);
    $this->post($post, [
        'password' => 'N3w-Str0ng-Passw0rd!',
        'password_confirmation' => 'N3w-Str0ng-Passw0rd!',
    ])->assertRedirect(route('login'));

    $user->refresh();
    expect($user->change_password)->toBeTruthy()
        ->and(Hash::check('N3w-Str0ng-Passw0rd!', $user->password))->toBeTrue();
});

// C2 — PUT profil/update/{user} : on ne modifie que son propre profil

it('forbids a customer from updating another user profile', function () {
    $admin = User::factory()->create(['role' => RoleEnum::ADMIN]);
    $customer = User::factory()->create(['role' => RoleEnum::CUSTOMER]);
    $adminEmail = $admin->email;
    $adminHash = $admin->password;

    $this->actingAs($customer)
        ->put("/profil/update/{$admin->id}", [
            'name' => 'Pwned',
            'email' => 'pwned@evil.test',
            'password' => 'N3w-Str0ng-Passw0rd!',
            'password_confirmation' => 'N3w-Str0ng-Passw0rd!',
        ])->assertForbidden();

    $admin->refresh();
    expect($admin->email)->toBe($adminEmail)
        ->and($admin->password)->toBe($adminHash);
});

it('lets a user update their own profile', function () {
    $customer = User::factory()->create(['role' => RoleEnum::CUSTOMER]);

    $this->actingAs($customer)
        ->put("/profil/update/{$customer->id}", [
            'name' => 'Nouveau Nom',
            'email' => $customer->email,
        ])->assertRedirect();

    expect($customer->fresh()->name)->toBe('Nouveau Nom');
});
