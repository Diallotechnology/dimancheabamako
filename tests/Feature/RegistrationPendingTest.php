<?php

declare(strict_types=1);

use App\Mail\ConfirmRegistrationMail;
use App\Models\PendingRegistration;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

// A3 — le formulaire d'inscription ne doit jamais remplacer l'inscription en attente d'un autre.

function registerPayload(array $extra = []): array
{
    return array_merge([
        'prenom' => 'Awa',
        'nom' => 'Traoré',
        'pays' => 'Mali',
        'contact' => '+22370123456',
        'email' => 'awa@example.com',
        'password' => 'Victim-Passw0rd1',
        'password_confirmation' => 'Victim-Passw0rd1',
    ], $extra);
}

it('opens a pending registration and sends the confirmation link', function () {
    Mail::fake();

    $this->post('/register', registerPayload())->assertRedirect();

    $pending = PendingRegistration::where('email', 'awa@example.com')->first();
    expect($pending)->not->toBeNull()
        ->and(Hash::check('Victim-Passw0rd1', $pending->password))->toBeTrue();
    Mail::assertSent(ConfirmRegistrationMail::class, fn ($mail) => PendingRegistration::hashToken($mail->token) === $pending->token);
});

it('does not let a second registration with the same e-mail replace the password or the data', function () {
    Mail::fake();
    $this->post('/register', registerPayload());
    $before = PendingRegistration::where('email', 'awa@example.com')->first();

    $this->post('/register', registerPayload([
        'password' => 'Attacker-Passw0rd1',
        'password_confirmation' => 'Attacker-Passw0rd1',
        'contact' => '+22370999999',
        'prenom' => 'Pirate',
    ]));

    $after = PendingRegistration::where('email', 'awa@example.com')->first();
    expect(PendingRegistration::count())->toBe(1)
        ->and(Hash::check('Victim-Passw0rd1', $after->password))->toBeTrue()
        ->and($after->prenom)->toBe('Awa')
        ->and($after->contact)->toBe('+22370123456');
});

it('sends a fresh link to the e-mail owner when the same e-mail registers again, invalidating the old one', function () {
    Mail::fake();
    $this->post('/register', registerPayload());
    $first = sentConfirmationToken();

    $this->post('/register', registerPayload());
    $second = sentConfirmationToken();

    expect($second)->not->toBe($first)
        ->and(PendingRegistration::count())->toBe(1)
        ->and(PendingRegistration::where('token', PendingRegistration::hashToken($second))->exists())->toBeTrue()
        ->and(PendingRegistration::where('token', PendingRegistration::hashToken($first))->exists())->toBeFalse();
    $this->get(route('confirm-registration', $first))->assertNotFound();
    $this->get(route('confirm-registration', $second))->assertOk();
});

it('stores only a hash of the token, never the clear value', function () {
    Mail::fake();
    $this->post('/register', registerPayload());
    $token = sentConfirmationToken();

    expect(PendingRegistration::value('token'))->toBe(hash('sha256', $token))->not->toBe($token);
});

it('does not hijack the pending registration of another e-mail through its phone number', function () {
    Mail::fake();
    $this->post('/register', registerPayload());
    $before = PendingRegistration::where('email', 'awa@example.com')->first();

    $this->post('/register', registerPayload(['email' => 'pirate@example.com', 'password' => 'Attacker-Passw0rd1', 'password_confirmation' => 'Attacker-Passw0rd1']))
        ->assertSessionHasErrors('contact');

    $after = PendingRegistration::where('email', 'awa@example.com')->first();
    expect(PendingRegistration::count())->toBe(1)
        ->and($after->token)->toBe($before->token)
        ->and($after->password)->toBe($before->password);
    Mail::assertNotSent(ConfirmRegistrationMail::class, fn ($mail) => $mail->hasTo('pirate@example.com'));
});
