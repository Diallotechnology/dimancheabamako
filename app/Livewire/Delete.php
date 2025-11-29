<?php

namespace App\Livewire;

use App\Helper\CartAction;

use Livewire\Component;

class Delete extends Component
{
    use CartAction;

    public int $id;

    public function deleteProduct()
    {
        app(CartAction::class)->destroy($this->id);
        $this->dispatch('productDelete');
        $this->dispatch('productCount')->to(Counter::class);
    }

    public function render()
    {
        return view('livewire.delete');
    }
}
