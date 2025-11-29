<?php

namespace App\Livewire;

use App\Helper\CartAction;

use Livewire\Component;

class Delete extends Component
{
    use CartAction;

    public int $id;

    public bool $isDeleting = false;

    public function deleteProduct()
    {
        $this->isDeleting = true; // disparition instantanée dans l'UI

        // Suppression backend
        $success = $this->cart->remove($this->id);

        if (!$success) {
            $this->isDeleting = false;
            flash()->warning('Suppression impossible.');
            return;
        }
        $this->dispatch('productDelete');
        $this->dispatch('productCount')->to(Counter::class);
    }

    public function render()
    {
        return view('livewire.delete');
    }
}
