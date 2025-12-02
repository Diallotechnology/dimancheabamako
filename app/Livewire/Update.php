<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Helper\CartAction;
use Illuminate\Support\Collection;
use Livewire\Component;

final class Update extends Component
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

    public function increment()
    {
        $this->quantity++;

        if ($this->quantity > $this->stock) {
            $this->quantity = $this->stock;
            flash()->warning('Stock insuffisant.');

            return;
        }

        $this->applyQuantity();
    }

    public function decrement()
    {
        $this->quantity--;

        if ($this->quantity < 1) {
            $this->quantity = 1;

            return;
        }

        $this->applyQuantity();
    }

    public function applyQuantity()
    {
        $newQuantity = (int) $this->quantity;

        $success = $this->cart->update($this->card['id'], $newQuantity);

        if (! $success) {
            $item = $this->cart->get($this->card['id']);
            $this->quantity = $item['quantity'];
            flash()->warning('Quantité invalide ou stock insuffisant.');
        }

        $this->dispatch('productUpdate');
    }

    public function render()
    {
        return view('livewire.update');
    }
}
