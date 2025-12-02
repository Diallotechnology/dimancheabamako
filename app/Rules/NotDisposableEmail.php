<?php

declare(strict_types=1);

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

final class NotDisposableEmail implements ValidationRule
{
    private array $disposableDomains = [
        '10minutemail.com',
        '10minutemail.net',
        'guerrillamail.com',
        'yopmail.com',
        'mailinator.com',
        'tempmail.com',
        'tempmail.net',
        'dispostable.com',
        'trashmail.com',
        // Ajoute ici les domaines que tu observes dans tes logs
    ];

    /**
     * Run the validation rule.
     *
     * @param  Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $domain = mb_strtolower(mb_substr(mb_strrchr($value, '@'), 1));

        if (in_array($domain, $this->disposableDomains, true)) {
            $fail('Cet email semble être une adresse jetable. Veuillez utiliser une adresse email valide.');
        }
    }
}
