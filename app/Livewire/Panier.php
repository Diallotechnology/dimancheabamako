<?php

namespace App\Livewire;

use App\Helper\CartAction;
use App\Models\Country;
use App\Models\Devise;
use App\Models\Shipping;
use Darryldecode\Cart\Facades\CartFacade;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;
use Livewire\Component;

class Panier extends Component
{
    use CartAction, LivewireAlert;

    public int $qte = 1;

    public int $country_id = 0;

    public $transport_id;

    public $shipping;

    public $trans = [];

    #[Validate('required|email')]
    public $email;

    #[Validate('required')]
    public $password;

    public function login_submit()
    {
        $this->validate();

        $credentials = [
            'email' => $this->email,
            'password' => $this->password,
        ];

        if (Auth::attempt($credentials)) {
            $this->alert(
                'success', 'Connexion reussi',
            );

            return $this->redirectRoute('panier');
        }

        $this->alert(
            'warning', __('auth.failed'),
        );
    }

    public function updatingCountryid()
    {
        $this->transport_id = '';
    }

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
        if (session('devise') === 'EUR') {
            $tauxConversion = session('devise') === 'EUR' ? Devise::whereType('EUR')->value('taux') : '';
            $Total = floatval(number_format(CartFacade::session($this->get_userid())->getTotal() / $tauxConversion, 2));
        } elseif (session('devise') === 'CFA') {
            $Total = number_format(CartFacade::session($this->get_userid())->getTotal(), 0, ',', ' ');
        }

        $totalWeight = $items->pluck('attributes')->sum('poids').' Kg';

        $country = Country::select('id', 'nom')->get();

        return view('livewire.panier', compact('items', 'TotalQuantity', 'Total', 'country', 'totalWeight'));
    }
}
