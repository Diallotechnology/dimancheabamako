<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Helper\CartAction;
use App\Models\Product;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;

final class QuickAddModal extends Component
{
    use CartAction;

    /**
     * Balisage à rendre : `legacy` (modale Bootstrap, layout Evara) ou `shop` (dialog Alpine, layout vitrine).
     * Transitoire : la variante `legacy` disparaît avec la purge d'Evara.
     */
    #[Locked]
    public string $variant = 'legacy';

    /** @var array<string, mixed> */
    public array $addedItem = [];

    public ?int $quickQuantity = null;

    /**
     * Point d'entrée unique de l'ajout au panier depuis les cartes produit :
     * `Livewire.dispatch('add-to-cart', { id })`.
     */
    #[On('add-to-cart')]
    public function addToCart(int $id): void
    {
        $this->quickQuantity = null;
        $this->addedItem = $this->store($id);

        $this->dispatch('productCount')->to(Counter::class);
    }

    public function setQuickQuantity(int $qty): void
    {
        // Sécurité : uniquement pour les produits sur commande
        if (! ($this->addedItem['is_preorder'] ?? false)) {
            return;
        }

        // Règle métier stricte
        if (! in_array($qty, [5, 6], true)) {
            session()->flash(
                'warning',
                __('messages.product_status.infos')
            );

            return;
        }

        $this->quickQuantity = $qty;

        $this->applyQuantity(
            (int) $this->addedItem['id'],
            $qty
        );
    }

    public function goToCart()
    {
        if (($this->addedItem['is_preorder'] ?? false) && $this->quickQuantity === null) {
            session()->flash(
                'warning',
                __('messages.product_status.infos')
            );

            return;
        }

        return redirect()->route('panier');
    }

    #[On('openQuickModal')]
    public function open(array $item): void
    {
        $this->addedItem = $item;
    }

    public function render(): View
    {
        return view($this->variant === 'shop' ? 'livewire.quick-add-modal-shop' : 'livewire.quick-add-modal', [
            'pricing' => $this->pricing(),
        ]);
    }

    /**
     * Prix de la ligne ajoutée, en CFA. `price` du panier est DÉJÀ remisé (CartAction::store, prix_final_base) :
     * on ne le remise pas une seconde fois ; le prix d'origine, utile seulement pour l'afficher barré, est relu en base.
     *
     * @return array{unit: int, original: ?int, total: int, reduction: int}
     */
    private function pricing(): array
    {
        $unit = (int) ($this->addedItem['price'] ?? 0);
        $reduction = (int) ($this->addedItem['attributes']['reduction'] ?? 0);
        $quantity = max(1, (int) ($this->addedItem['quantity'] ?? 1));

        $original = null;
        if ($reduction > 0 && isset($this->addedItem['id'])) {
            $prix = Product::query()->whereKey((int) $this->addedItem['id'])->value('prix');
            $original = is_numeric($prix) && (int) $prix > $unit ? (int) $prix : null;
        }

        return ['unit' => $unit, 'original' => $original, 'total' => $unit * $quantity, 'reduction' => $reduction];
    }

    private function applyQuantity(int|string $rowId, int $qty): void
    {
        // Cart::update attend un delta
        $this->cart->update($rowId, $qty);

        // Sync UI
        $this->addedItem['quantity'] = $qty;
    }
}
