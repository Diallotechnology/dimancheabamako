<?php

namespace App\Livewire;

use App\Helper\CartAction;
use Darryldecode\Cart\Facades\CartFacade;
use Illuminate\Support\Collection;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Livewire\Component;

class Update extends Component
{
    use CartAction, LivewireAlert;

    public string $qte_min = '';

    public int $stock = 0;

    public $quantity;

    public Collection $card;

    public function mount(Collection $row): void
    {
        $this->card = $row;
        $this->stock = $row->associatedModel->stock;
        $this->quantity = $row->quantity;
    }

    public function updatedQuantity()
    {
        $this->update();
    }

    public function update(): void
    {

        $product = CartFacade::session($this->get_userid())->get($this->card['id']);
        // si qte inferieure a 1
        if ($this->quantity < 1) {
            // qte  = 1
            CartFacade::session($this->get_userid())->update($this->card['id'], [
                'quantity' => [
                    'relative' => false,
                    'value' => 1,
                ],
            ]);
            $this->alert('warning', 'Quantité minimun est de 1!');
        }

        // si qte superieure au stock
        if ($this->quantity > $product->associatedModel->stock) {
            $this->alert('warning', 'Quantité non disponible!');
        }

        // si qte inferieure au stock et si qte > = 1
        if ($this->quantity <= $product->associatedModel->stock and $this->quantity >= 1) {
            // add qte demander
            CartFacade::session($this->get_userid())->update($this->card['id'], [
                'quantity' => [
                    'relative' => false,
                    'value' => $this->quantity,
                ],
                'attributes' => ['poids' => $this->quantity * $product->associatedModel->poids],
            ]);
        }
        $this->dispatch('productUpdate');
    }

    public function deleteProduct()
    {
        $product = CartFacade::session($this->get_userid())->get($this->card['id']);
        CartFacade::session($this->get_userid())->remove($product->id);

        return $this->alert(
            'success', 'Produit supprimer du panier avec success!',
        );
    }

    public function render()
    {
        return view('livewire.update');
    }
}
