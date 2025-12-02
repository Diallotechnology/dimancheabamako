<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Helper\CartAction;
use App\Rules\ValidShoppingCart;
use App\Service\CartService;
use Livewire\Component;

final class AddToCard extends Component
{
    use CartAction;

    public $id;

    public function add()
    {
        $this->validate([
            'checkout' => [new ValidShoppingCart(app(CartService::class))],
        ]);

        return $this->store($this->id);
    }

    public function render()
    {
        return view('livewire.add-to-card');
    }
}
