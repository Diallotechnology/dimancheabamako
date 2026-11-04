<?php

declare(strict_types=1);

use App\Enum\RoleEnum;
use App\Models\User;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\URL;

function verificationUrlFor(User $user): string
{
    return URL::temporarySignedRoute('verification.verify', now()->addMinutes(60), [
        'id' => $user->id,
        'hash' => sha1($user->getEmailForVerification()),
    ]);
}

it('verifies the email of an unverified customer and redirects with the verified flag', function () {
    $user = User::factory()->unverified()->create(['role' => RoleEnum::CUSTOMER]);

    $this->actingAs($user)->get(verificationUrlFor($user))->assertRedirect('/?verified=1');

    expect($user->fresh()->hasVerifiedEmail())->toBeTrue();
});

it('redirects an already verified user without error', function () {
    $user = User::factory()->create(['role' => RoleEnum::CUSTOMER]);

    $this->actingAs($user)->get(verificationUrlFor($user))->assertRedirect('/?verified=1');
});

it('redirects back to the verification notice instead of crashing when the email cannot be marked as verified', function () {
    $user = User::factory()->unverified()->create(['role' => RoleEnum::CUSTOMER]);
    Event::listen('eloquent.updating: '.User::class, fn () => false);

    $this->actingAs($user)
        ->get(verificationUrlFor($user))
        ->assertRedirect(route('verification.notice'));

    expect($user->fresh()->hasVerifiedEmail())->toBeFalse();
});
