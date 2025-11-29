<?php

namespace App\Livewire;

use App\Models\Product;
use Livewire\Component;
use App\Models\Category;
use App\Helper\CartAction;
use Livewire\WithPagination;
use Livewire\Attributes\Locked;
use App\Service\CategoryService;
use Illuminate\Support\Facades\Cache;

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
    }

    public function render()
    {
        $rows = Product::query()->with('promotions')->ByStock()->when($this->search, function ($query) {
            $query->whereAny(['nom', 'color'], 'LIKE', '%' . $this->search . '%');
        })->when($this->cat, function ($query) {
            $query->where('categorie_id', $this->cat->id);
        })->latest('id')->paginate(15);

        return view('livewire.produit', compact('rows'));
    }
}
