<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Support\Facades\RateLimiter;

// A7 — un mot de passe essayé sur N comptes différents (password spraying) depuis une même IP doit être bloqué.

it('locks an IP after 20 failures spread over different accounts', function () {
    RateLimiter::clear('login-ip|127.0.0.1');

    foreach (range(1, 20) as $i) {
        $this->post(route('login'), ['email' => "victime{$i}@example.com", 'password' => 'Spray123x'])
            ->assertSessionHasErrors('email');
    }

    $user = User::factory()->create(['password' => bcrypt('Secret123x')]);

    $this->post(route('login'), ['email' => $user->email, 'password' => 'Secret123x'])
        ->assertSessionHasErrors('email');
    $this->assertGuest();
});

it('does not lock other IPs nor a normal login', function () {
    $user = User::factory()->create(['password' => bcrypt('Secret123x')]);

    $this->post(route('login'), ['email' => $user->email, 'password' => 'Secret123x']);

    $this->assertAuthenticatedAs($user);
});
