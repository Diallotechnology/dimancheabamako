<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature');

/**
 * Jeton en clair du dernier e-mail de confirmation d'inscription (la base n'en garde que le hash).
 */
function sentConfirmationToken(): string
{
    return (string) Illuminate\Support\Facades\Mail::sent(App\Mail\ConfirmRegistrationMail::class)->last()->token;
}
