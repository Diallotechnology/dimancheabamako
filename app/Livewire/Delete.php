<?php

namespace App\Livewire;

use App\Helper\CartAction;
use Darryldecode\Cart\Facades\CartFacade;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Livewire\Component;

class Delete extends Component
{
    use CartAction, LivewireAlert;

    public $id;

    public function deleteProduct()
    {
        $product = CartFacade::session($this->get_userid())->get($this->id);
        CartFacade::session($this->get_userid())->remove($product->id);
        $this->dispatch('productDelete');
        $this->dispatch('productCount')->to(Counter::class);
        $this->alert(
            'success', 'Produit supprimer du panier avec success!',
        );
    }

    public function render()
    {
        return view('livewire.delete');
    }
}
