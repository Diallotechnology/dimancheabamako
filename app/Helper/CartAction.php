<?php

declare(strict_types=1);

namespace App\Helper;

use App\Models\Country;
use App\Models\Product;
use App\Models\Shipping;
use App\Livewire\Counter;
use App\Service\CartService;
use App\Rules\ValidShoppingCart;
use Darryldecode\Cart\Facades\CartFacade;
use Jantinnerezo\LivewireAlert\LivewireAlert;

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
        $product = Product::without('categorie')->findOrFail($id);
        $product->append(['prix_final']);
        $cartItems = $this->cart->getContent();
        if ($cartItems->has($product->id)) {
            flash()->warning('Produit existe déjà dans le panier.');
            return;
        }
        $this->cart->add(
            $product->id,
            $product->nom,
            $product->getPrixFinal(),
            $product->stock,
            $product->poids,
            $product->toArray()
        );
        flash()->success('Produit ajouter au panier avec success.');

        $this->dispatch('productCount')->to(Counter::class);

        $this->dispatch('productAdded');
    }

    public function getWeight(bool $format = false): float|string
    {
        $total = $this->cart->getContent()
            ->map(function ($item) {

                $weight = $item['poids'] ?? 0;

                // Convertir les chaînes avec virgule → float (1,23 → 1.23)
                if (is_string($weight)) {
                    $weight = floatval(str_replace(',', '.', $weight));
                }

                return $weight * $item['quantity'];
            })
            ->sum();
        return  $format ? number_format($total, 2, '.', ' ') . ' kg' : $total;
    }

    public function getShippingCost(int $countryId, int $transportId): ?Shipping
    {
        // poids total
        $totalWeight = $this->getWeight();

        if ($totalWeight <= 0) {
            return null; // panier vide ou poids invalide
        }

        // zone du pays
        $zoneId = Country::findOrFail($countryId)->zone_id;

        // récupérer la correspondance shipping
        return Shipping::query()
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
