<?php

declare(strict_types=1);

namespace App\Helper;

use App\Models\Product;
use App\Service\PriceService;

final class ProductView
{
    public static function fromModel(Product $product, PriceService $pricing): array
    {
        return [
            'id' => $product->id,
            'slug' => $product->slug,
            'nom' => $product->nom,
            'reference' => $product->reference,
            'cover' => $product->cover,
            'taille' => $product->taille,
            'color' => $product->color,
            'categorie' => $product->categorie?->nom,
            'reduction' => $product->reduction ?? 0,
            'prix_promo' => $pricing->formatPromo($product),   // string prêt à afficher
            'prix_format' => $pricing->formatBase($product),    // string prêt à afficher
        ];
    }

    /**
     * Convertit une collection de Product en array de tableaux plats.
     */
    public static function collection(iterable $products, PriceService $pricing): array
    {
        $result = [];

        foreach ($products as $product) {
            $result[] = self::fromModel($product, $pricing);
        }

        return $result;
    }
}
