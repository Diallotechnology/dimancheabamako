<?php

declare(strict_types=1);

namespace App\Service;

use App\Enum\RoleEnum;
use App\Models\Client;
use App\Models\Country;
use App\Models\Order;
use App\Models\Shipping;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

final class OrderService
{
    public function createOrder($request, CartService $cart): Order
    {
        return DB::transaction(function () use ($request, $cart) {

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
            $shipping = Shipping::findOrFail($request->integer('livraison'));

            $totalWeight = $cart->getContent()
                ->pluck('attributes.poids')
                ->sum();

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

            // 4. vérifier stock + attach
            foreach ($cart->getContent() as $item) {

                if ($item['is_preorder'] === false and $item['quantity'] > $item['stock']) {
                    throw new Exception("Stock insuffisant pour {$item['name']}");
                }

                $order->products()->attach(
                    (int) $item['id'],
                    [
                        'quantity' => $item['quantity'],
                        'montant' => $item['price'] * $item['quantity'],
                    ]
                );
            }

            return $order;
        });
    }
}
