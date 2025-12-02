<?php

declare(strict_types=1);

namespace App\Service;

use App\Models\Product;

final class PriceService
{
    public function formatBase(Product $product): string
    {
        $devise = session('devise', 'CFA');
        $taux = session('taux_eur', 1);
        $prix = $product->prix;

        if ($devise === 'EUR') {
            return number_format($prix / $taux, 2, ',', ' ').' €';
        }

        return number_format($prix, 0, ',', ' ').' CFA';
    }

    public function formatPromo(Product $product): string
    {
        $devise = session('devise', 'CFA');
        $taux = session('taux_eur', 1);

        $prix = $product->prix_final_base; // ton accessor numérique côté modèle

        if ($devise === 'EUR') {
            return number_format($prix / $taux, 2, ',', ' ').' €';
        }

        return number_format($prix, 0, ',', ' ').' CFA';
    }
}
