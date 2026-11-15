<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Helper\CartAction;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\On;
use Livewire\Component;

final class Counter extends Component
{
    use CartAction;

    /**
     * Écouteur vide : recevoir l'événement suffit à déclencher un nouveau rendu (donc un nouveau compte).
     * L'attribut ne doit pas être posé sur render() : il en fait une « action » et casse la mise à jour du composant.
     */
    #[On('productCount')]
    public function refreshCount(): void {}

    public function render(): View
    {
        $count = $this->cart->getCount();

        return view('livewire.counter', ['count' => $count]);
    }
}
