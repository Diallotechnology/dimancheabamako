<?php

namespace App\Livewire;

use App\Helper\CartAction;
use App\Models\Category;
use App\Models\Product as ModelsProduct;
use Livewire\Component;
use Livewire\WithPagination;

class Product extends Component
{
    use CartAction, WithPagination;

    public function add(int $id)
    {
        return $this->store($id);
    }

    public function render()
    {
        $rows = ModelsProduct::paginate(15);
        $category = Category::all();

        return view('livewire.product', compact('rows', 'category'));
    }
}
