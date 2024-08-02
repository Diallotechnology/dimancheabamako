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

    private function get_userid(): string
    {
        if (! auth()->check()) {
            // Utilisateur non authentifié
            if (! empty(session('user_id'))) {
                // Utiliser l'identifiant de session existant
                $userId = session()->get('user_id');
            } else {
                // Générer un UUID pour le panier
                $userId = uniqid();
                session()->put('user_id', $userId);
            }
        } else {
            // Utilisateur authentifié
            $userId = (string) auth()->user()->id;
            session()->put('user_id', $userId);
        }

        return $userId;
    }

    private function cart_clear(): void
    {
        if (! empty(session('user_id'))) {
            // Utiliser l'identifiant de session existant
            $userId = session()->get('user_id');
            CartFacade::session($userId)->clear();
        }
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
