<?php

namespace App\Livewire;

use Livewire\Component;
use App\Helper\CartAction;
use App\Service\CartService;
use App\Rules\ValidShoppingCart;

class AddToCard extends Component
{
    use CartAction;

    public $id;

    public function add()
    {
        $this->validate([
            'checkout' => [new ValidShoppingCart(app(CartService::class))]
        ]);

        return $this->store($this->id);
    }

    public function render()
    {
        return view('livewire.add-to-card');
    }
}
