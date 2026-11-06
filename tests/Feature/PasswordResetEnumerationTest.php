<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\RateLimiter;

// A6 — « mot de passe oublié » ne doit pas révéler si un e-mail correspond à un compte.

beforeEach(function (): void {
    Notification::fake();
    RateLimiter::clear('forgot-password');
});

it('answers identically for a known and an unknown email', function () {
    $user = User::factory()->create();

    $known = $this->post(route('password.email'), ['email' => $user->email]);
    $unknown = $this->post(route('password.email'), ['email' => 'inconnu@example.com']);

    $known->assertSessionHasNoErrors();
    $unknown->assertSessionHasNoErrors();
    expect(session('status'))->toBe(__('messages.reset_link_neutral'));
    $known->assertSessionHas('status', __('messages.reset_link_neutral'));
    $unknown->assertSessionHas('status', __('messages.reset_link_neutral'));
    expect($known->getStatusCode())->toBe($unknown->getStatusCode());
});

it('sends the reset notification only to an existing account', function () {
    $user = User::factory()->create();

    $this->post(route('password.email'), ['email' => 'inconnu@example.com']);
    Notification::assertNothingSent();

    $this->post(route('password.email'), ['email' => $user->email]);
    Notification::assertSentTo($user, ResetPassword::class);
});

it('throttles the forgot-password route', function () {
    foreach (range(1, 5) as $i) {
        $this->post(route('password.email'), ['email' => "x{$i}@example.com"])->assertStatus(302);
    }

    $this->post(route('password.email'), ['email' => 'x6@example.com'])->assertStatus(429);
});
