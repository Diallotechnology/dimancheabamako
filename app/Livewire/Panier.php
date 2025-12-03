<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Helper\CartAction;
use App\Models\Country;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;
use Livewire\Component;

final class Panier extends Component
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

    public int $count = 0;

    public int $TotalQuantity = 0;

    public string $Total = '0';

    public string $totalWeight = '0';

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
        }
        $this->trans = [];

        return flash()->success('Nous ne livrons pas dans ce pays!');
    }

    public function calculateShipping()
    {
        $shipping = $this->getShippingCost($this->country_id, $this->transport_id);

        if (! $shipping) {
            $this->shipping = null;
            flash()->warning('Aucune correspondance trouvée pour le transport choisi.');

            return;
        }

        $this->shipping = $shipping;
    }

    public function mount(): void
    {
        $this->refreshCart();
    }

    #[On('productUpdate')]
    #[On('productDelete')]
    public function refreshCart(): void
    {
        $this->count = $this->cart->getCount();
        $this->TotalQuantity = $this->cart->getTotalQuantity();
        $this->totalWeight = $this->getWeight(true);

        if (session('devise') === 'EUR') {
            $taux = session('taux_eur', 1);
            $this->Total = number_format($this->cart->getTotal() / $taux, 2, ',', ' ');
        } else {
            $this->Total = number_format($this->cart->getTotal(), 0, ',', ' ');
        }
    }

    public function render()
    {
        $items = $this->cart->getContent();

        $country = Country::select('id', 'nom')->get();

        return view('livewire.panier', compact('items', 'country'));
    }
}
