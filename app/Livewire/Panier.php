<?php

namespace App\Livewire;

use App\Helper\CartAction;
use App\Models\Country;
use App\Models\Devise;
use App\Models\Shipping;
use Darryldecode\Cart\Facades\CartFacade;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Livewire\Attributes\On;
use Livewire\Component;

class Panier extends Component
{
    use CartAction, LivewireAlert;

    public int $qte = 1;

    public int $country_id;

    public $transport_id;

    public $shipping;

    public $trans = [];

    public function updatingCountryid()
    {
        $this->transport_id = '';
    }

    #[On('Test')]
    public function GetTrans()
    {
        // get country
        $country = Country::with('zone')->find($this->country_id);
        if ($country->zone->transports->isNotEmpty()) {
            return $this->trans = $country->zone->transports;
        } else {
            $this->trans = [];

            return $this->alert(
                'warning', 'Nous ne livrons pas dans ce pays!',
            );
        }
    }

    public function GetShipping()
    {
        // Retrieve the total weight of the products in the cart
        $items = CartFacade::session($this->get_userid())->getContent()->sortBy('name');
        $totalWeight = $items->pluck('attributes')->sum('poids');
        // get zone id
        $pays = Country::findOrFail($this->country_id);
        try {
            // Fetch the shipping rule based on the country ID, transport ID, and weight range
            $this->shipping = Shipping::whereZoneId($pays->zone_id)
                ->whereTransportId($this->transport_id)
                ->whereRelation('poids', function ($query) use ($totalWeight) {
                    $query->where('min', '<=', $totalWeight)->where('max', '>=', $totalWeight);
                })->firstOrFail();

        } catch (ModelNotFoundException $e) {
            // Handle case where shipping rule is not found
            return $this->alert(
                'warning', 'Aucune correspondance trouvé!',
            );
        }
    }

    #[On('productUpdate')]
    #[On('productDelete')]
    public function render()
    {
        // get panier content
        $items = CartFacade::session($this->get_userid())->getContent()->sortBy('name');
        // get total qte
        $TotalQuantity = CartFacade::session($this->get_userid())->getTotalQuantity();
        // get total price
        $tauxConversion = session('locale') === 'fr' ? Devise::whereType('EUR')->value('taux') : Devise::whereType('USD')->value('taux');
        $Total = floatval(number_format(CartFacade::session($this->get_userid())->getTotal() / $tauxConversion, 2));

        $totalWeight = $items->pluck('attributes')->sum('poids').' Kg';
        $country = Country::all('nom', 'id');

        return view('livewire.panier', \compact('items', 'TotalQuantity', 'Total', 'country', 'totalWeight'));
    }
}
