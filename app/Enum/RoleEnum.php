<?php

declare(strict_types=1);

namespace App\Enum;

enum RoleEnum: string
{
    // case SUPERADMIN = 'Superadmin';
    case ADMIN = 'Administrateur';
    case SECRTETAIRE = 'Secretaire';
    case CUSTOMER = 'Client';
}
