<?php

declare(strict_types=1);

namespace App\Helper;

use App\Livewire\Counter;
use App\Models\Product;
use Darryldecode\Cart\Facades\CartFacade;
use Jantinnerezo\LivewireAlert\LivewireAlert;

trait CartAction
{
    use LivewireAlert;

    private function get_userid(): string|int
    {
        if (! auth()->check()) {
            if (session()->has('user_id')) {
                $userId = session()->get('user_id');
            } else {
                $userId = uniqid();
                session()->put('user_id', $userId);
            }
        } else {
            $userId = auth()->user()->id;
        }

        return $userId;
    }

    public function store(int $id)
    {
        $product = Product::findOrFail($id);
        $product->append(['prix_final']);
        $productSelected = $product;
        $productAdded = [
            'id' => $productSelected->id,
            'name' => $productSelected->nom,
            'price' => $productSelected->getPrixFinal(),
            'quantity' => 1,
            'attributes' => ['poids' => $productSelected->poids],
            'associatedModel' => $productSelected,
        ];

        $cartNotEmpty = ! CartFacade::session($this->get_userid())->isEmpty();

        if ($cartNotEmpty && CartFacade::session($this->get_userid())->getContent()->containsStrict('id', $productSelected->id)) {
            return $this->alert(
                'warning', 'Produit existe deja dans le panier!',
            );
        }

        CartFacade::session($this->get_userid())->add($productAdded);
        if ($cartNotEmpty) {
            $this->dispatch('productCount')->to(Counter::class);
        }
        $this->dispatch('productCount')->to(Counter::class);

        $this->dispatch('productAdded');
    }
}
