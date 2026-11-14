<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Helper\CartAction;
use App\Models\Client;
use App\Models\Country;
use App\Service\CartService;
use App\Service\LoginService;
use App\Service\PriceService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Component;

#[Layout('layouts::shop')]
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

    public string $totalWeight = '0';

    public function login_submit()
    {
        $this->validate();

        $oldSessionId = session()->getId(); // ⚠️ Crutial

        // Mêmes règles que le login principal : limitation partagée (ValidationException → erreur sur `email`).
        $login = app(LoginService::class);

        try {
            $user = $login->attempt((string) $this->email, (string) $this->password, false, (string) request()->ip());
        } catch (ValidationException $e) {
            // Le mot de passe ne doit pas rester dans le snapshot Livewire après un échec.
            $this->reset('password');

            throw $e;
        }

        if (! $user->change_password) {
            Auth::logout();

            return $this->redirect($login->changePasswordUrl($user));
        }

        app(CartService::class)->mergeGuestCartToUser($user->id, $oldSessionId);

        flash()->success('Connexion réussie.');

        return $this->redirectRoute('panier');
    }

    public function updatingCountryId(): void
    {
        $this->transport_id = '';
        $this->shipping = null;
    }

    public function updatedCountryId(): void
    {
        $this->GetTrans();
    }

    public function updatedTransportId(): void
    {
        $this->calculateShipping();
    }

    public function GetTrans()
    {
        $country = Country::with('zone.transports')->find($this->country_id);

        if ($country?->zone?->transports->isNotEmpty()) {
            $this->trans = $country->zone->transports;

            // Un seul transporteur possible : inutile de le demander au client.
            if ($this->trans->count() === 1) {
                $this->transport_id = (string) $this->trans->first()->id;
                $this->calculateShipping();
            }

            return $this->trans;
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
        $this->prefillKnownDelivery();
    }

    public function getHasPreorderProperty()
    {
        return $this->cart->getContent()->contains('is_preorder', true);
    }

    public function refreshCart(): void
    {
        $this->count = $this->cart->getCount();
        $this->TotalQuantity = $this->cart->getTotalQuantity();
        $this->totalWeight = $this->getWeight(true);
    }

    /**
     * Retire une ligne du panier de la session courante (l'id ne peut viser que ce panier).
     */
    public function remove(int $id): void
    {
        if (! $this->cart->remove($id)) {
            flash()->warning('Suppression impossible.');

            return;
        }

        $this->refreshCart();
        $this->dispatch('productCount')->to(Counter::class);
    }

    /**
     * Total affiché au client : sous-total du panier + frais de livraison, dans la devise de session.
     */
    public function getTotalWithShippingProperty(): string
    {
        return PriceService::format((float) $this->cart->getTotal() + (float) ($this->shipping?->montant ?? 0));
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

    /**
     * Client connecté : le pays de sa dernière commande est pré-sélectionné (et le transporteur s'il est unique).
     */
    private function prefillKnownDelivery(): void
    {
        if (! Auth::check() || ! Auth::user()->isClient()) {
            return;
        }

        $countryId = Client::query()
            ->where('email', Auth::user()->email)
            ->first()?->latestOrder?->country_id;

        if ($countryId !== null && $this->cart->getContent()->isNotEmpty()) {
            $this->country_id = (string) $countryId;
            $this->GetTrans();
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
}
