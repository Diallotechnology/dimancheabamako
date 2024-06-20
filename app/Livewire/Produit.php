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

    public string $cat = '';

    public function add(int $id)
    {
        return $this->store($id);
    }

    public function mount(?Category $category = null)
    {
        // dd($category);
        $this->cat = $category->id;
    }

    public function render()
    {
        $rows = Product::ByStock()->when($this->search, function ($query) {
            $query->whereAny(['nom', 'color'], 'LIKE', '%'.$this->search.'%');
        })->when($this->cat, function ($query) {
            $query->where('categorie_id', $this->cat);
        })->latest('id')->paginate(15);
        $category_list = Category::all();

        return view('livewire.produit', compact('rows', 'category_list'));
    }
}
