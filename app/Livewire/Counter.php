<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Helper\CartAction;
use Livewire\Attributes\On;
use Livewire\Component;

final class Counter extends Component
{
    use CartAction;

    #[On('productCount')]
    public function render()
    {
        $count = $this->cart->getCount();

        return view('livewire.counter', compact('count'));
    }
}
