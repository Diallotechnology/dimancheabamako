<?php

declare(strict_types=1);

use App\Enum\OrderEnum;
use App\Enum\RoleEnum;
use App\Models\Category;
use App\Models\Client;
use App\Models\Country;
use App\Models\Order;
use App\Models\Product;
use App\Models\Transport;
use App\Models\User;
use App\Models\Zone;
use Illuminate\Support\Carbon;

function dashboardOrder(string $state, Carbon $date, int $montant): Order
{
    $zone = Zone::firstOrCreate(['nom' => 'Afrique']);
    $order = Order::create([
        'client_id' => Client::firstOrCreate(['email' => 'awa@example.test'], [
            'prenom' => 'Awa', 'nom' => 'Traoré', 'contact' => '70000000', 'pays' => 'Mali',
        ])->id,
        'country_id' => Country::firstOrCreate(['nom' => 'Mali'], ['zone_id' => $zone->id])->id,
        'transport_id' => Transport::firstOrCreate(['nom' => 'DHL'])->id,
        'reference' => uniqid('dash'),
        'adresse' => 'Rue 12',
        'ville' => 'Bamako',
        'poids' => '1',
        'shipping' => '15000',
        'etat' => OrderEnum::SAVE->value,
        'trans_state' => $state,
    ]);
    $order->forceFill(['created_at' => $date])->save();

    $order->products()->attach(Product::factory()->create(['categorie_id' => Category::factory()->create()->id])->id, ['quantity' => 1, 'montant' => $montant]);

    return $order;
}

it('computes revenu and ventes on the same PURCHASED orders of the current month and year', function () {
    $now = now()->startOfMonth()->addDays(2);

    dashboardOrder('PURCHASED', $now, 10000);
    dashboardOrder('PURCHASED', $now->copy(), 5000);
    dashboardOrder('PENDING', $now->copy(), 99000);              // non réglée : exclue
    dashboardOrder('PURCHASED', $now->copy()->subYear(), 70000); // même mois, année précédente : exclue

    $this->actingAs(User::factory()->create(['role' => RoleEnum::SECRTETAIRE]))
        ->get('/admin/dashboard')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Admin/Dashboard')
            ->where('revenu', 15000)
            ->where('order', 2));

    expect(Order::count())->toBe(4);
});
