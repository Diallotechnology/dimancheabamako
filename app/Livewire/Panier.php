<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Helper\CartAction;
use App\Models\Country;
use App\Service\CartService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;
use Livewire\Component;

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
        if ($country->zone->transports->isNotEmpty()) {
            return $this->trans = $country->zone->transports;
        }
        $this->trans = [];

        return flash()->success(__('messages.panier.not_deliver'));
    }

    public function calculateShipping()
    {
        $shipping = $this->getShippingCost((int) $this->country_id, (int) $this->transport_id);

        if (! $shipping) {
            $this->shipping = null;
            flash()->warning(__('messages.panier.not_transport'));

            return;
        }

        $this->shipping = $shipping;
    }

    public function mount(): void
    {
        $this->refreshCart();
        $this->cleanInvalidPreorders();
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

    public function render()
    {
        $items = $this->cart->getContent();

        $country = Country::select('id', 'nom')->get();

        return view('livewire.panier', compact('items', 'country'));
    }
}
