<?php

declare(strict_types=1);

namespace App\Helper;

use App\Models\Product;
use App\Models\Shipping;
use App\Service\CartService;
use Illuminate\Support\Facades\DB;

trait CartAction
{
    protected CartService $cart;

    public function initializeCartAction(CartService $cart): void
    {
        $this->cart = $cart;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id)
    {
        if ($this->cart->remove($id)) {
            flash()->success('Produit supprimé du panier.');
        }
    }

    public function store(int $id)
    {
        $product = Product::with([
            'promotions' => fn($q) => $q->active()->orderByDesc('id')->limit(1),
        ])->findOrFail($id);

        // 1. Ajouter ou récupérer l'item
        if ($this->cart->has($product->id)) {
            $added = $this->cart->get($product->id); // déjà existant
        } else {
            $added = $this->cart->add(
                id: $product->id,
                name: $product->nom,
                price: $product->prix_final_base,
                stock: $product->stock,
                poids: $product->poids,
                is_preorder: $product->is_preorder,
                attributes: [
                    'cover' => $product->cover,
                    'reduction' => $product->reduction ?? 0,
                ]
            );
        }


        // 2. Structurer pour le front
        $item = [
            'id' => $added['id'],
            'name' => $added['name'],
            'price' => $added['price'],
            'quantity' => $added['quantity'],
            'poids' => $added['poids'],
            'stock' => $added['stock'],
            'is_preorder' => $added['is_preorder'],
            'attributes' => [
                'cover' => $added['attributes']['cover'] ?? null,
                'reduction' => $added['attributes']['reduction'] ?? 0,
            ],
        ];

        // 3. Ouverture immédiate du modal (toujours)
        $this->js('window.showQuick()');

        // 4. Envoi du payload (toujours)
        $this->dispatch('openQuickModal', item: $item);

        return $item; // utile si besoin dans add()
    }

    public function getWeight(bool $format = false): float|string
    {
        $total = $this->cart->getContent()
            ->map(function ($item) {

                $weight = $item['poids'] ?? 0;

                // Convertir les chaînes avec virgule → float (1,23 → 1.23)
                if (is_string($weight)) {
                    $weight = (float) (str_replace(',', '.', $weight));
                }

                return $weight * $item['quantity'];
            })
            ->sum();

        return $format ? number_format($total, 2, '.', ' ') . ' kg' : $total;
    }

    public function getShippingCost(int $countryId, int $transportId): ?Shipping
    {
        // poids total
        $totalWeight = $this->getWeight();

        if ($totalWeight <= 0) {
            return null; // panier vide ou poids invalide
        }

        // zone du pays
        $zoneId = DB::table('countries')->select('zone_id', 'id')->where('id', $countryId)->value('zone_id');

        // récupérer la correspondance shipping
        return Shipping::query()
            ->select('id', 'zone_id', 'transport_id', 'montant', 'temps')
            ->where('zone_id', $zoneId)
            ->where('transport_id', $transportId)
            ->whereHas('poids', function ($query) use ($totalWeight) {
                $query
                    ->where('min', '<=', $totalWeight)
                    ->where('max', '>=', $totalWeight);
            })
            ->first();
    }

    public function getShippingAmount(int $countryId, int $transportId): ?int
    {
        $shipping = $this->getShippingCost($countryId, $transportId);

        return $shipping?->price;
    }
}
