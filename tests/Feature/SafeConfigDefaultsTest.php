<?php

declare(strict_types=1);

// M7 — valeurs par défaut sûres : debug désactivé dans le gabarit, cookie de session Secure en production

function sessionConfigWith(string $appEnv, ?string $secureCookie): array
{
    // Le dépôt d'environnement de Laravel est immuable : on pilote les superglobales qu'il lit.
    $keys = ['APP_ENV' => $appEnv, 'SESSION_SECURE_COOKIE' => $secureCookie];
    $saved = [];

    foreach ($keys as $key => $value) {
        $saved[$key] = [$_ENV[$key] ?? null, $_SERVER[$key] ?? null];

        if ($value === null) {
            unset($_ENV[$key], $_SERVER[$key]);
        } else {
            $_ENV[$key] = $_SERVER[$key] = $value;
        }
    }

    try {
        return require config_path('session.php');
    } finally {
        foreach ($saved as $key => [$env, $server]) {
            $env === null ? Illuminate\Support\Arr::forget($_ENV, $key) : $_ENV[$key] = $env;
            $server === null ? Illuminate\Support\Arr::forget($_SERVER, $key) : $_SERVER[$key] = $server;
        }
    }
}

it('ships .env.example with debug disabled', function () {
    expect(file_get_contents(base_path('.env.example')))->toContain('APP_DEBUG=false');
});

it('marks the session cookie Secure by default in production', function () {
    expect(sessionConfigWith('production', null)['secure'])->toBeTrue();
});

it('keeps the session cookie usable over http outside production', function () {
    expect(sessionConfigWith('local', null)['secure'])->toBeFalsy();
});

it('lets SESSION_SECURE_COOKIE override the default', function () {
    expect(sessionConfigWith('production', 'false')['secure'])->toBeFalse();
});
