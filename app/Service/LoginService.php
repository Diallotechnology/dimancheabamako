<?php

declare(strict_types=1);

namespace App\Service;

use App\Models\User;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Source unique de la connexion par e-mail / mot de passe (login principal et login du panier) :
 * limitation de tentatives partagée, tentative d'authentification, URL du premier changement de mot de passe.
 */
final class LoginService
{
    private const MAX_ATTEMPTS = 5;

    /**
     * @throws ValidationException si trop de tentatives ou identifiants invalides
     */
    public function attempt(string $email, string $password, bool $remember, string $ip): User
    {
        $key = $this->throttleKey($email, $ip);

        if (RateLimiter::tooManyAttempts($key, self::MAX_ATTEMPTS)) {
            event(new Lockout(request()));

            $seconds = RateLimiter::availableIn($key);

            throw ValidationException::withMessages([
                'email' => trans('auth.throttle', [
                    'seconds' => $seconds,
                    'minutes' => ceil($seconds / 60),
                ]),
            ]);
        }

        if (! Auth::attempt(['email' => $email, 'password' => $password], $remember)) {
            RateLimiter::hit($key);

            throw ValidationException::withMessages([
                'email' => trans('auth.failed'),
            ]);
        }

        RateLimiter::clear($key);

        /** @var User $user */
        $user = Auth::user();

        return $user;
    }

    /**
     * URL signée (30 min) de la page de premier changement de mot de passe.
     */
    public function changePasswordUrl(User $user): string
    {
        return URL::temporarySignedRoute('change.password', now()->addMinutes(30), ['email' => $user->email]);
    }

    private function throttleKey(string $email, string $ip): string
    {
        return Str::transliterate(Str::lower($email).'|'.$ip);
    }
}
