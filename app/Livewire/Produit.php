<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Helper\CartAction;
use App\Models\Category;
use App\Models\Product;
use Livewire\Component;
use Livewire\WithPagination;

final class Produit extends Component
{
    use CartAction, WithPagination;

    public string $search = '';

    public Category $cat;

    public function add(int $id)
    {
        return $this->store($id);
    }

    public function mount(Category $category)
    {
        $this->cat = $category;
    }

    public function render()
    {
        $rows = Product::query()->with('promotions', 'categorie:id,nom')->ByStock()->when($this->search, function ($query) {
            $query->whereAny(['nom', 'color'], 'LIKE', '%' . $this->search . '%');
        })->when($this->cat, function ($query) {
            $query->where('categorie_id', $this->cat->id);
        })->latest('id')->paginate(15);

        return view('livewire.produit', compact('rows'));
    }
}
