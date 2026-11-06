<?php

declare(strict_types=1);

use App\Models\PendingRegistration;
use App\Models\User;
use App\Service\PendingRegistrationService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

// Régression : le mot de passe saisi doit fonctionner après confirmation (pas de double hachage).

it('keeps the chosen password usable once the registration is confirmed', function () {
    Mail::fake();
    app(PendingRegistrationService::class)->request([
        'prenom' => 'Awa', 'nom' => 'Traoré', 'pays' => 'Mali',
        'contact' => '+22370000001', 'email' => 'awa@example.com',
    ], 'Secret123x', 'fr');

    $this->post('/confirm-registration/'.PendingRegistration::value('token'));

    $user = User::where('email', 'awa@example.com')->firstOrFail();
    expect(Hash::check('Secret123x', $user->password))->toBeTrue();
});
