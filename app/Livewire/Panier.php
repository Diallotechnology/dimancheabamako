<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\Client;
use App\Models\Country;
use Livewire\Component;
use App\Helper\CartAction;
use Livewire\Attributes\On;
use App\Service\CartService;
use Livewire\Attributes\Validate;
use Illuminate\Support\Facades\Auth;

final class Panier extends Component
{
    use CartAction;

    public $country_id;

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

        $oldSessionId = session()->getId(); // ⚠️ Crutial
        if (Auth::attempt([
            'email' => $this->email,
            'password' => $this->password,
        ])) {

            $cart = app(CartService::class);
            $cart->mergeGuestCartToUser(Auth::id(), $oldSessionId);

            flash()->success('Connexion réussie.');

            return $this->redirectRoute('panier');
        }

        flash()->error(__('auth.failed'));
    }

    public function updatingCountryid()
    {
        $this->transport_id = '';
    }

    public function GetTrans()
    {
        // get country
        $country = Country::with('zone')->find($this->country_id);
        $country->loadMissing('zone.transports');
        if ($country->zone->transports->isNotEmpty()) {
            return $this->trans = $country->zone->transports;
        }
        $this->trans = [];
        session()->flash('warning', __('messages.panier.not_deliver'));
        return flash()->success(__('messages.panier.not_deliver'));
    }

    public function calculateShipping()
    {
        $this->shipping = $this->getShippingCost((int) $this->country_id, (int) $this->transport_id);

        if (! $this->shipping) {
            $this->shipping = null;
            session()->flash('warning', __('messages.panier.not_transport'));
            flash()->warning(__('messages.panier.not_transport'));

            return;
        }
        return $this->shipping;
    }

    public function mount(): void
    {
        $this->refreshCart();
        $this->cleanInvalidPreorders();
    }

    public function getHasPreorderProperty()
    {
        return $this->cart->getContent()->contains('is_preorder', true);
    }

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

    private function cleanInvalidPreorders(): void
    {
        $this->cart->getContent()->each(function ($item) {
            if (
                ($item['is_preorder'] ?? false) === true
                && ! in_array($item['quantity'], [5, 6], true)
            ) {
                $this->cart->remove($item['id']);
            }
        });
    }

    public function render()
    {
        $items = $this->cart->getContent();

        $country = Country::select('id', 'nom')->get();

        if (Auth::check() and Auth::user()->isClient()) {
            $client = Client::with('latestOrder')
                ->where('email', Auth::user()->email)
                ->first();
        } else {
            $client = null;
        }

        return view('livewire.panier', compact('items', 'country', 'client'));
    }
}
