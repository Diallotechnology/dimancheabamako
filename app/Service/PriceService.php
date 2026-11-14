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

    /**
     * Formate un montant stocké en CFA dans la devise de la session (EUR si taux valide, sinon CFA).
     * Point unique de conversion pour l'affichage : ne pas refaire `number_format(... eurRate())` ailleurs.
     */
    public static function format(int|float $amountCfa): string
    {
        $rate = self::isEuro() ? self::eurRate() : null;

        if ($rate !== null) {
            return number_format($amountCfa / $rate, 2, ',', ' ').' €';
        }

        return number_format($amountCfa, 0, ',', ' ').' CFA';
    }

    public function formatBase(Product $product): string
    {
        return self::format($product->prix);
    }

    public function formatPromo(Product $product): string
    {
        return self::format($product->prix_final_base);
    }
}
