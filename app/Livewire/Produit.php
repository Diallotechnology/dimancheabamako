<?php

namespace App\Livewire;

use App\Helper\CartAction;
use App\Models\Category;
use App\Models\Product;
use Livewire\Component;
use Livewire\WithPagination;

class Produit extends Component
{
    use CartAction, WithPagination;

    public string $search = '';

    public string $categorie = '';

    public function add(int $id)
    {
        return $this->store($id);
    }

    public function render()
    {
        $rows = Product::when($this->search, function ($query, $search) {
            $query->whereAny(['nom', 'color'], 'LIKE', '%'.$search.'%');
        })->when($this->categorie, function ($query, $category) {
            $query->where('categorie_id', $category->id);
        })->latest('id')->paginate(15);
        $category = Category::all();

        return view('livewire.produit', compact('rows', 'category'));
    }
}
