<?php

declare(strict_types=1);

namespace App\Service;

use App\Enum\RoleEnum;
use App\Exceptions\StaleCartException;
use App\Models\Client;
use App\Models\Country;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use DomainException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

final class OrderService
{
    public function createOrder($request, CartService $cart): Order
    {
        return DB::transaction(function () use ($request, $cart) {

            // 0. Le panier vient du cache (24 h) : prix, promo, stock et poids sont relus en base,
            // produits verrouillés, AVANT tout calcul (poids de livraison, totaux, montant N-Genius).
            $products = Product::query()
                ->whereIn('id', $cart->getContent()->keys())
                ->with('activePromotion')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            if ($cart->refreshFromProducts($products)) {
                throw new StaleCartException('Le prix ou la disponibilité d\'un produit a changé.');
            }

            foreach ($cart->getContent() as $item) {
                if ($item['is_preorder'] === false && $item['quantity'] > $item['stock']) {
                    throw new StaleCartException("Stock insuffisant pour {$item['name']}");
                }
            }

            // 1. Pays + contact normalisé
            $country = Country::findOrFail($request->country_id);
            $contactE164 = phone($request->contact)->formatE164();

            // 2. Résoudre le client (source de vérité)
            $client = Client::where('contact', $contactE164)->first()
                ?? Client::where('email', $request->email)->first();

            $client = app(ClientResolverService::class)->resolve([
                'prenom' => $request->prenom,
                'nom' => $request->nom,
                'contact' => phone($request->contact)->formatE164(),
                'email' => $request->email,
                'pays' => Country::findOrFail($request->country_id)->nom,
            ]);

            if ($request->filled('password')) {
                $user = User::where('email', $request->email)->first();

                if (! $user) {
                    $user = User::create([
                        'name' => "{$request->prenom} {$request->nom}",
                        'email' => $request->email,
                        'password' => Hash::make($request->password),
                        'role' => RoleEnum::CUSTOMER->value,
                        'client_id' => $client->id,
                    ]);
                } elseif ($user->client_id === null) {
                    $user->update(['client_id' => $client->id]);
                }
            }

            // 3. infos commande
            // Le tarif est recalculé côté serveur ; l'ID `livraison` du formulaire ne sert
            // qu'à vérifier que le client a bien vu (et accepté) ce tarif-là.
            $shipping = app(ShippingService::class)->resolve(
                (int) $country->id,
                (int) $request->transport_id,
                $cart->getTotalWeight(),
            );

            if ($shipping === null || $shipping->id !== $request->integer('livraison')) {
                throw new DomainException('Tarif de livraison invalide pour cette commande.');
            }

            $totalWeight = $cart->getTotalWeight();

            $order = $client->orders()->create([
                'adresse' => $request->adresse,
                'postal' => $request->postal,
                'ville' => $request->ville,
                'country_id' => $country->id,
                'poids' => $totalWeight.' Kg',
                'shipping' => $shipping->montant,
                'transport_id' => $request->transport_id,
                'commentaire' => $request->commentaire,
            ]);

            // 4. attach + réservation du stock (prix et stock déjà revalidés, produits verrouillés).
            // Restitué si la commande est annulée, refusée ou expire (Order::releaseStock).
            foreach ($cart->getContent() as $item) {
                $order->products()->attach(
                    (int) $item['id'],
                    [
                        'quantity' => $item['quantity'],
                        'montant' => $item['price'] * $item['quantity'],
                    ]
                );

                if ($item['is_preorder'] === false) {
                    $products[(int) $item['id']]->decrement('stock', (int) $item['quantity']);
                }
            }

            return $order;
        });
    }
}
