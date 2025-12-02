<?php

declare(strict_types=1);

namespace App\Enum;

enum ProductStatus: string
{
    case AVAILABLE = 'Disponible';
    case UNAVAILABLE = 'Indisponible';
    case COMMANDE = 'Precommande';
}
