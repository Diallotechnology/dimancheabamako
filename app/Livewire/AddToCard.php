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
    public array $addedItem = [];
    public $id;

    public function add()
    {
        $item = $this->store((int)$this->id);

        $this->addedItem = [
            'rowId' => $item['rowId'],
            'id' => $item['id'],
            'name' => $item['name'],
            'price' => $item['price'],
            'quantity' => $item['quantity'],
        ];
        return;
    }

    public function render()
    {
        return view('livewire.add-to-card');
    }
}
