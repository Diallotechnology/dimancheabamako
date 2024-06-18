<?php

namespace App\Livewire;

use App\Helper\CartAction;
use Livewire\Component;

class AddToCard extends Component
{
    use CartAction;

    public $id;

    public function add()
    {
        return $this->store($this->id);
    }

    public function render()
    {
        return view('livewire.add-to-card');
    }
}
