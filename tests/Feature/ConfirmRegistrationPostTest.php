<?php

declare(strict_types=1);

use App\Models\PendingRegistration;
use App\Models\User;
use App\Service\PendingRegistrationService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

// A8 — le lien d'e-mail (GET) n'affiche qu'une page ; seul le POST crée le compte et connecte.

function openPending(): string
{
    Mail::fake();
    app(PendingRegistrationService::class)->request([
        'prenom' => 'Awa', 'nom' => 'Traoré', 'pays' => 'Mali',
        'contact' => '+22370000001', 'email' => 'awa@example.com',
    ], 'Secret123x', 'fr');

    return (string) PendingRegistration::value('token');
}

it('only shows a confirmation page on GET, without consuming the token', function () {
    $token = openPending();

    $this->get(route('confirm-registration', $token))->assertOk()->assertSee(route('confirm-registration.store', $token));

    expect(User::where('email', 'awa@example.com')->exists())->toBeFalse()
        ->and(PendingRegistration::where('token', $token)->exists())->toBeTrue();
    $this->assertGuest();
});

it('creates the account, logs in and consumes the token on POST', function () {
    $token = openPending();

    $this->post(route('confirm-registration.store', $token))->assertRedirect();

    $user = User::where('email', 'awa@example.com')->firstOrFail();
    expect(Hash::check('Secret123x', $user->password))->toBeTrue()
        ->and(PendingRegistration::where('token', $token)->exists())->toBeFalse();
    $this->assertAuthenticatedAs($user);
});

it('answers 404 for an unknown or expired token, on GET and POST', function () {
    $token = openPending();
    PendingRegistration::query()->update(['expires_at' => now()->subMinute()]);

    $this->get(route('confirm-registration', $token))->assertNotFound();
    $this->post(route('confirm-registration.store', $token))->assertNotFound();
    $this->get(route('confirm-registration', str_repeat('z', 64)))->assertNotFound();
    expect(User::count())->toBe(0);
});
