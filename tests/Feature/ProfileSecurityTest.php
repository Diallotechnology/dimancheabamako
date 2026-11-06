<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Support\Facades\Hash;

// A4 — changer l'e-mail ou le mot de passe exige le mot de passe actuel, et invalide les autres sessions.

function profileUser(): User
{
    return User::factory()->create(['password' => 'Current-Passw0rd1', 'email' => 'moi@example.com'])->fresh();
}

function updateProfile($test, User $user, array $data)
{
    return $test->actingAs($user)->put("/profil/update/{$user->id}", array_merge(['name' => $user->name, 'email' => $user->email], $data));
}

it('refuses an e-mail change without the current password', function () {
    $user = profileUser();

    updateProfile($this, $user, ['email' => 'pirate@example.com'])->assertSessionHasErrors('current_password');

    expect($user->fresh()->email)->toBe('moi@example.com');
});

it('refuses an e-mail change with a wrong current password', function () {
    $user = profileUser();

    updateProfile($this, $user, ['email' => 'pirate@example.com', 'current_password' => 'wrong-password1'])
        ->assertSessionHasErrors('current_password');

    expect($user->fresh()->email)->toBe('moi@example.com');
});

it('refuses a password change without the current password', function () {
    $user = profileUser();
    $hash = $user->password;

    updateProfile($this, $user, ['password' => 'Brand-New-Passw0rd1', 'password_confirmation' => 'Brand-New-Passw0rd1'])
        ->assertSessionHasErrors('current_password');

    expect($user->fresh()->password)->toBe($hash);
});

it('lets a user change their e-mail and password with the current password', function () {
    $user = profileUser();

    updateProfile($this, $user, [
        'email' => 'nouveau@example.com',
        'current_password' => 'Current-Passw0rd1',
        'password' => 'Brand-New-Passw0rd1',
        'password_confirmation' => 'Brand-New-Passw0rd1',
    ])->assertSessionHasNoErrors();

    $user->refresh();
    expect($user->email)->toBe('nouveau@example.com')
        ->and(Hash::check('Brand-New-Passw0rd1', $user->password))->toBeTrue();
});

it('does not ask for the current password to change only the name', function () {
    $user = profileUser();

    updateProfile($this, $user, ['name' => 'Autre Nom'])->assertSessionHasNoErrors();

    expect($user->fresh()->name)->toBe('Autre Nom');
});

it('logs out other sessions after a password change but keeps the current one', function () {
    $user = profileUser();
    $oldHash = $user->password;

    $this->actingAs($user)->withSession(['password_hash_web' => $oldHash])
        ->put("/profil/update/{$user->id}", [
            'name' => $user->name,
            'email' => $user->email,
            'current_password' => 'Current-Passw0rd1',
            'password' => 'Brand-New-Passw0rd1',
            'password_confirmation' => 'Brand-New-Passw0rd1',
        ])->assertSessionHasNoErrors();

    // La session qui a changé le mot de passe reste valide…
    $this->get('/profil')->assertOk();

    // …alors qu'une autre session, restée sur l'ancien hash, est déconnectée.
    $this->flushSession();
    $this->actingAs($user->fresh())->withSession(['password_hash_web' => $oldHash])
        ->get('/profil')->assertRedirect(route('login'));
});
