<?php

namespace App\Livewire;

use Darryldecode\Cart\Facades\CartFacade;
use Livewire\Attributes\On;
use Livewire\Component;

class Counter extends Component
{
    private function get_userid(): string
    {
        if (! auth()->check()) {
            if (session()->has('user_id')) {
                $userId = session()->get('user_id');
            } else {
                $userId = \uniqid();
                session()->put('user_id', $userId);
            }
        } else {
            $userId = auth()->user()->id;
        }

        return $userId;
    }

    #[On('productCount')]
    public function render()
    {
        $count = CartFacade::session($this->get_userid())->getContent()->count();

        return view('livewire.counter', compact('count'));
    }
}
