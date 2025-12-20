<?php

declare(strict_types=1);

namespace App\Service;

use const false;

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

            // 1. enregistrer user si besoin
            if ($request->filled('password')) {
                User::firstOrCreate(
                    ['email' => $request->email],
                    [
                        'name' => $request->prenom.' '.$request->nom,
                        'email' => $request->email,
                        'password' => Hash::make($request->password),
                        'change_password' => true,
                        'role' => RoleEnum::CUSTOMER->value,
                    ]
                );
            }

            // 2. enregistrer client
            $country = Country::findOrFail($request->country_id);

            $client = Client::firstOrCreate(
                ['email' => $request->email],
                [
                    'prenom' => $request->prenom,
                    'nom' => $request->nom,
                    'contact' => $request->contact,
                    'pays' => $country->nom,
                ]
            );

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
