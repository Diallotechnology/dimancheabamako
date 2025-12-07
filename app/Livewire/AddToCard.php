<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Helper\CartAction;
use Livewire\Component;

final class AddToCard extends Component
{
    use CartAction;

    public $id;

    public function add()
    {
        $this->store((int) $this->id);
    }

    public function render()
    {
        return view('livewire.add-to-card');
    }
}
