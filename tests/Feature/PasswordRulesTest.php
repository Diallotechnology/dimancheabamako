<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

// A5 — Password::defaults() est configuré une seule fois (AppServiceProvider) : 8 caractères, lettres + chiffres.

function passwordFails(string $password): bool
{
    return Validator::make(['password' => $password], ['password' => [Password::defaults()]])->fails();
}

it('rejects weak passwords', function (string $weak) {
    expect(passwordFails($weak))->toBeTrue();
})->with([
    'trop court' => 'Ab1xyz',
    'sans chiffre' => 'motdepasse',
    'sans lettre' => '12345678',
]);

it('accepts letters and digits without further constraints', function (string $ok) {
    expect(passwordFails($ok))->toBeFalse();
})->with([
    'minuscules + chiffres' => 'motdepasse1',
    'avec majuscule' => 'Str0ng-Passw0rd!',
]);
