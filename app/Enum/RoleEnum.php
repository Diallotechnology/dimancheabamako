<?php

declare(strict_types=1);

namespace App\Enum;

enum RoleEnum: string
{
    case ADMIN = 'Administrateur';
    case SECRTETAIRE = 'Secretaire';
    case CUSTOMER = 'Client';

    /**
     * Libellé affiché dans les listes de sélection du back-office.
     */
    public function label(): string
    {
        return match ($this) {
            self::ADMIN => 'Administrateur (accès à tout)',
            self::SECRTETAIRE => 'Secrétaire (accès limité)',
            self::CUSTOMER => 'Client',
        };
    }
}
