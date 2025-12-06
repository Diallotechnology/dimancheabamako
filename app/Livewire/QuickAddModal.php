<?php

namespace App\Livewire;

use Livewire\Component;
use App\Helper\CartAction;
use Livewire\Attributes\On;
use Illuminate\Support\Collection;

class QuickAddModal extends Component
{
    use CartAction;
    /** @var array<int, array<string, mixed>> */
    public array $addedItem = [];

    private function updateQuantity($rowId, $newQty)
    {
        $line = $this->cart->get($rowId);

        if ($newQty > $line['stock']) {
            flash()->warning('Stock insuffisant…');
            return false;
        }

        if ($newQty < 1) {
            flash()->warning('Quantité minimale 1.');
            return false;
        }

        $this->cart->update($rowId, $newQty);
        return $newQty;
    }



    public function increaseQuick($rowId)
    {
        if ($newQty = $this->updateQuantity($rowId, $this->cart->get($rowId)['quantity'] + 1)) {
            $this->addedItem['quantity'] = $newQty;
        }
    }

    public function decreaseQuick($rowId)
    {
        if ($newQty = $this->updateQuantity($rowId, $this->cart->get($rowId)['quantity'] - 1)) {
            $this->addedItem['quantity'] = $newQty;
        }
    }



    #[On('openQuickModal')]
    public function open(array $item)
    {
        $this->addedItem = $item;

        if (empty($this->addedItem)) {
            return;
        }
        // $this->dispatch('showQuickModalJS');
    }


    public function render()
    {
        return view('livewire.quick-add-modal');
    }
}
