<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Helper\ProductView;
use App\Models\Category;
use App\Models\Product;
use App\Service\PriceService;
use Illuminate\Contracts\View\View;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Catalogue d'une catégorie (`/shop/{category}-{slug}`). L'ajout au panier passe par l'événement
 * `add-to-cart` des cartes produit, reçu par QuickAddModal.
 */
#[Layout('layouts::shop')]
final class ProductList extends Component
{
    use WithPagination;

    private const PER_PAGE = 15;

    #[Url(as: 'q', history: false)]
    public string $search = '';

    public Category $cat;

    public function mount(Category $category): void
    {
        $this->cat = $category;
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    /**
     * @return LengthAwarePaginator<int, array<string, mixed>>
     */
    #[Computed]
    public function products(): LengthAwarePaginator
    {
        $search = mb_trim($this->search);
        $pricing = app(PriceService::class);

        return Product::query()
            ->published()
            ->byStock()
            ->with('promotions', 'categorie:id,nom')
            ->where('categorie_id', $this->cat->id)
            ->when($search !== '', fn ($query) => $query->whereAny(['nom', 'color'], 'LIKE', '%'.$search.'%'))
            ->latest('id')
            ->paginate(self::PER_PAGE)
            ->through(fn (Product $product): array => ProductView::fromModel($product, $pricing));
    }

    public function render(): View
    {
        return view('livewire.product-list');
    }
}
