<?php

namespace App\Livewire;

use App\Helper\CartAction;
use Darryldecode\Cart\Facades\CartFacade;
use Illuminate\Support\Collection;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Livewire\Component;

class Update extends Component
{
    use CartAction;

    public int $stock = 0;

    public int|string $quantity;

    public Collection $card;

    public function mount(Collection $row): void
    {
        $this->card = $row;
        $this->stock = $row['stock'];
        $this->quantity = $row['quantity'];
    }

    public function updatedQuantity()
    {
        // Quantité entrée au clavier → on convertit en entier
        $newQuantity = (int) $this->quantity;

        $success = $this->cart->update($this->card['id'], $newQuantity);

        if (!$success) {
            // Rétablir la valeur correcte si quantité invalide
            $item = $this->cart->get($this->card['id']);
            $this->quantity = $item['quantity'];
            flash()->warning("Quantité invalide ou stock insuffisant.");
        }

        // Mise à jour affichage live
        $this->dispatch('productUpdate');
    }

    public function render()
    {
        return view('livewire.update');
    }
}
