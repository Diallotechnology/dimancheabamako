<?php

namespace App\Livewire;

use Livewire\Component;

use App\Helper\CartAction;
use Illuminate\Support\Collection;

class Delete extends Component
{
    use CartAction;

    public Collection $row;

    public bool $isDeleting = false;

    public function deleteProduct()
    {
        $this->isDeleting = true; // disparition instantanée dans l'UI

        // Suppression backend
        $success = $this->cart->remove($this->row['id']);

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
