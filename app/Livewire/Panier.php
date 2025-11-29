<?php

namespace App\Livewire;

use App\Models\Devise;
use App\Models\Country;
use Livewire\Component;
use App\Models\Shipping;
use App\Helper\CartAction;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Darryldecode\Cart\Facades\CartFacade;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class Panier extends Component
{
    use CartAction;

    public int $qte = 1;

    public int $country_id = 0;

    public $transport_id;

    public $shipping;

    public $trans = [];

    #[Validate('required|email')]
    public $email;

    #[Validate('required')]
    public $password;

    /** @var \Illuminate\Support\Collection */
    public Collection $items;
    public int $count = 0;
    public int $TotalQuantity = 0;
    public string $Total = '0';
    public string $totalWeight = '0';
    public Collection $country;

    public function login_submit()
    {
        $this->validate();

        $credentials = [
            'email' => $this->email,
            'password' => $this->password,
        ];

        if (Auth::attempt($credentials)) {
            flash()->success('Connexion reussi.');

            return $this->redirectRoute('panier');
        }
        flash()->success(__('auth.failed'));
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

            return flash()->success('Nous ne livrons pas dans ce pays!');
        }
    }

    public function calculateShipping()
    {
        $shipping = $this->getShippingCost($this->country_id, $this->transport_id);

        if (!$shipping) {
            $this->shipping = null;
            flash()->warning('Aucune correspondance trouvée pour le transport choisi.');
            return;
        }

        $this->shipping = $shipping;
    }
    // public function GetShipping()
    // {
    //     // Retrieve the total weight of the products in the cart
    //     $items = $this->cart->getContent();
    //     $totalWeight = $items->getWeight();
    //     // get zone id
    //     $pays = Country::findOrFail($this->country_id);
    //     try {
    //         // Fetch the shipping rule based on the country ID, transport ID, and weight range
    //         $this->shipping = Shipping::whereZoneId($pays->zone_id)
    //             ->whereTransportId($this->transport_id)
    //             ->whereRelation('poids', function ($query) use ($totalWeight) {
    //                 $query->where('min', '<=', $totalWeight)->where('max', '>=', $totalWeight);
    //             })->firstOrFail();
    //     } catch (ModelNotFoundException $e) {
    //         // Handle case where shipping rule is not found
    //         return flash()->warning('Aucune correspondance trouvé!');
    //     }
    // }



    public function mount(): void
    {
        $this->refreshCart();
    }

    #[On('productUpdate')]
    #[On('productDelete')]
    public function refreshCart(): void
    {
        $this->items = $this->cart->getContent();
        $this->count = $this->cart->getCount();
        $this->TotalQuantity = $this->cart->getTotalQuantity();

        if (session('devise') === 'EUR') {
            $tauxConversion = Devise::whereType('EUR')->value('taux');
            $this->Total = number_format($this->cart->getTotal() / $tauxConversion, 2, ',', ' ');
        } else { // par défaut CFA
            $this->Total = number_format($this->cart->getTotal(), 0, ',', ' ');
        }

        $this->totalWeight = $this->getWeight(true);
        $this->country = Country::select('id', 'nom')->get();
    }
}
