<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Helper\CartAction;
use Livewire\Component;

final class CartItem extends Component
{
    use CartAction;

    public bool $hot = false;

    public bool $news = false;

    /** @var array<int, array<string, mixed>> */
    public array $items = [];

    public function mount(array $items, bool $hot = false, bool $news = false): void
    {
        // Livewire ne veut que des arrays / scalaires
        $this->items = $items;
        $this->hot = $hot;
        $this->news = $news;
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
