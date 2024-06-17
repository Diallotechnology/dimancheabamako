<?php

namespace App\Livewire;

use App\Helper\CartAction;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Component;

class CartItem extends Component
{
    use CartAction;

    public Collection $items;

    public bool $hot = false;

    public bool $news = false;

    public function mount($items)
    {
        $this->items = $items;
    }

    public function add(int $id)
    {
        return $this->store($id);
    }

    public function render()
    {
        return view('livewire.cart-item');
    }
}
