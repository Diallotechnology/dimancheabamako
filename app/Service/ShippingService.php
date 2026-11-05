<?php

declare(strict_types=1);

namespace App\Service;

use App\Models\Country;
use App\Models\Shipping;

/**
 * Source unique de résolution du tarif de livraison (zone du pays + transport + tranche de poids).
 */
final class ShippingService
{
    public function resolve(int $countryId, int $transportId, float $weight): ?Shipping
    {
        if ($weight <= 0) {
            return null; // panier vide ou poids invalide
        }

        $zoneId = Country::query()->whereKey($countryId)->value('zone_id');

        if ($zoneId === null) {
            return null;
        }

        return Shipping::query()
            ->select('id', 'zone_id', 'transport_id', 'poids_id', 'montant', 'temps')
            ->where('zone_id', $zoneId)
            ->where('transport_id', $transportId)
            ->whereHas('poids', function ($query) use ($weight) {
                $query
                    ->where('min', '<=', $weight)
                    ->where('max', '>=', $weight);
            })
            ->first();
    }
}
