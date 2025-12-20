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

    public ?int $quickQuantity = null;

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
    public function open(array $item)
    {
        $this->addedItem = $item;

        if (empty($this->addedItem)) {
            return;
        }
    }

    public function render()
    {
        return view('livewire.quick-add-modal');
    }

    private function applyQuantity(int|string $rowId, int $qty): void
    {

        // Cart::update attend un delta
        $this->cart->update($rowId, $qty);

        // Sync UI
        $this->addedItem['quantity'] = $qty;
    }
}
