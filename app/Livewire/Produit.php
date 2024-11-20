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

    public Category $cat;

    public function add(int $id)
    {
        return $this->store($id);
    }

    public function mount(Category $category, string $slug)
    {
        $this->cat = $category;
        if ($category->nom !== $slug) {
            return redirect()->route('shop', ['category' => $category->id, 'slug' => $category->nom]);
        }
    }

    public function render()
    {
        $rows = Product::ByStock()->when($this->search, function ($query) {
            $query->whereAny(['nom', 'color'], 'LIKE', '%'.$this->search.'%');
        })->when($this->cat, function ($query) {
            $query->where('categorie_id', $this->cat->id);
        })->latest('id')->paginate(15);
        $category_list = Category::select('id', 'nom')->get();

        return view('livewire.produit', compact('rows', 'category_list'));
    }
}
