<?php

declare(strict_types=1);

namespace App\Enum;

enum RoleEnum: string
{
    // case SUPERADMIN = 'Superadmin';
    case ADMIN = 'Administrateur';
    case TEACHER = 'Professeur';
    case STUDENT = 'Etudiant';
    case PARENT = 'Parent';
}
