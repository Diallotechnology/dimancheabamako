<?php

declare(strict_types=1);

namespace App\Service;

use App\Models\Product;
use Illuminate\Support\Str;

final class ProductReferenceGenerator
{
    /**
     * Référence unique de type AI-20261105-K3F9QX (les références manuelles restent libres).
     */
    public function generate(): string
    {
        do {
            $reference = 'AI-'.now()->format('Ymd').'-'.Str::upper(Str::random(6));
        } while (Product::query()->where('reference', $reference)->exists());

        return $reference;
    }
}
