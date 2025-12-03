<?php

declare(strict_types=1);

namespace App\Service;

use App\Models\Country;
use App\Models\Shipping;

final class ShippingService
{
    public function getShippingCost(int $countryId, int $transportId, float $weight): ?Shipping
    {
        $zoneId = Country::findOrFail($countryId)->zone_id;

        return Shipping::query()
            ->where('zone_id', $zoneId)
            ->where('transport_id', $transportId)
            ->whereHas(
                'poids',
                fn ($q) => $q->where('min', '<=', $weight)
                    ->where('max', '>=', $weight)
            )
            ->first();
    }
}
