<?php

namespace App\Livewire;

use App\Helper\CartAction;
use Darryldecode\Cart\Facades\CartFacade;
use Livewire\Attributes\On;
use Livewire\Component;

class Counter extends Component
{
    use CartAction;

    #[On('productCount')]
    public function render()
    {
        $count = $this->cart->getCount();
        return view('livewire.counter', compact('count'));
    }
}
