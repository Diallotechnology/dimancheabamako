<?php

declare(strict_types=1);

namespace App\Exceptions;

use DomainException;

/**
 * Le panier (copié dans le cache) ne correspond plus aux produits en base :
 * prix modifié, promotion expirée, stock insuffisant ou produit supprimé.
 */
final class StaleCartException extends DomainException {}
