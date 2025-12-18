<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Helper\CartAction;
use Livewire\Attributes\On;
use Livewire\Component;

final class QuickAddModal extends Component
{
    use CartAction;

    /** @var array<int, array<string, mixed>> */
    public array $addedItem = [];

    public function setQuickQuantity(int $qty): void
    {
        // Validation directe
        if ($qty < 5 || $qty > 6) {
            session()->flash('warning', 'Quantité minimale 5 et maximale 6.');

            return;
        }

        // Mise à jour
        $this->cart->update((int)$this->addedItem['id'], $qty);

        // Mise à jour du modal
        $this->addedItem['quantity'] = $qty;
    }

    #[On('openQuickModal')]
    public function open(array $item)
    {
        $this->addedItem = $item;

        if (empty($this->addedItem)) {
            return;
        }
    }

    private function updateQuantity($rowId, $requestedQty): int|bool
    {
        $line = $this->cart->get($rowId);

        $statut = $line['is_preorder'] ?? false; // produit sur commande ?
        $stock = $line['stock'];
        $current = $line['quantity'];

        // Cas produit sur commande → quantités autorisées : 5 ou 7
        if ($statut === true) {

            // Trouver la quantité autorisée la plus proche
            $target = $requestedQty <= 5 ? 5 : 6;

            // Déjà au maximum
            if ($current >= 6 && $requestedQty > $current) {
                session()->flash('warning', __('messages.product_status.infos'));

                return false;
            }

            // Si aucun changement, inutile de mettre à jour
            if ($current === $target) {
                return $current;
            }

            // Mise à jour précise (Cart::update attend un delta, pas une valeur)
            $this->cart->update($rowId, $target - $current);

            return $target;
        }

        $this->cart->update($rowId, $requestedQty);

        return $requestedQty;
    }

    public function render()
    {
        return view('livewire.quick-add-modal');
    }
}
