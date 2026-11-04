<?php

declare(strict_types=1);

namespace App\Service;

use App\Models\Product;
use Illuminate\Support\Facades\Cache;

final class PriceService
{
    /**
     * Taux EUR valide (> 0) depuis la session puis le cache, sinon null.
     */
    public static function eurRate(): ?float
    {
        foreach ([session('taux_eur'), Cache::get('taux_eur')] as $taux) {
            if (is_numeric($taux) && (float) $taux > 0) {
                return (float) $taux;
            }
        }

        return null;
    }

    /**
     * Affichage en EUR seulement si la devise choisie est EUR ET qu'un taux valide existe (fallback CFA).
     */
    public static function isEuro(): bool
    {
        return session('devise') === 'EUR' && self::eurRate() !== null;
    }

    public function formatBase(Product $product): string
    {
        $prix = $product->prix;

        if (self::isEuro()) {
            return number_format($prix / self::eurRate(), 2, ',', ' ').' €';
        }

        return number_format($prix, 0, ',', ' ').' CFA';
    }

    public function formatPromo(Product $product): string
    {
        $prix = $product->prix_final_base; // ton accessor numérique côté modèle

        if (self::isEuro()) {
            return number_format($prix / self::eurRate(), 2, ',', ' ').' €';
        }

        return number_format($prix, 0, ',', ' ').' CFA';
    }
}
