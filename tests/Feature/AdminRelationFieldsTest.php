<?php

declare(strict_types=1);

use App\Enum\RoleEnum;
use App\Models\Category;
use App\Models\Product;
use App\Models\Promotion;
use App\Models\Transport;
use App\Models\User;
use App\Models\Zone;

// `product_id`, `pays` et `zone_id` sont des relations, pas des colonnes : ils ne doivent jamais atteindre
// create()/update(). Hors production, Model::shouldBeStrict() lève sinon MassAssignmentException (500) ;
// en production ils sont ignorés sans bruit. Dans les deux cas la relation doit être persistée.

/** @return array{0: Product, 1: Product} */
function twoProducts(): array
{
    $category = Category::factory()->create();
    [$a, $b] = Product::factory()->count(2)->create(['categorie_id' => $category->id]);

    return [$a, $b];
}

beforeEach(function () {
    $this->actingAs(User::factory()->create(['role' => RoleEnum::ADMIN]));
});

it('creates a promotion and attaches its products', function () {
    [$a, $b] = twoProducts();

    $this->post('/admin/promotion', [
        'nom' => 'Soldes',
        'reduction' => '15',
        'debut' => '2026-11-20T14:30',
        'fin' => '2026-11-27T18:30',
        'product_id' => [(string) $a->id, (string) $b->id],
    ])->assertSessionHasNoErrors()->assertRedirect();

    $promotion = Promotion::firstOrFail();
    expect($promotion->reduction)->toBe(15)
        ->and($promotion->products()->pluck('products.id')->sort()->values()->all())->toBe([$a->id, $b->id])
        ->and($promotion->debutat())->toBe('20/11/2026 14:30')
        ->and($promotion->finat())->toBe('27/11/2026 18:30');
});

it('updates a promotion and syncs its products', function () {
    [$a, $b] = twoProducts();
    $promotion = Promotion::factory()->create(['reduction' => 5]);
    $promotion->products()->attach($a->id);

    $this->patch("/admin/promotion/{$promotion->id}", [
        'nom' => 'Renommée',
        'reduction' => '40',
        'debut' => '2026-12-01T09:00',
        'fin' => '2026-12-05T21:15',
        'product_id' => [(string) $b->id],
    ])->assertSessionHasNoErrors()->assertRedirect();

    $promotion->refresh();
    expect($promotion->nom)->toBe('Renommée')
        ->and($promotion->reduction)->toBe(40)
        ->and($promotion->debutat())->toBe('01/12/2026 09:00')
        ->and($promotion->finat())->toBe('05/12/2026 21:15')
        ->and($promotion->products()->pluck('products.id')->all())->toBe([$b->id]);
});

it('creates a zone and saves its countries by name', function () {
    $this->post('/admin/zone', ['nom' => 'Afrique de l\'Ouest', 'pays' => ['Mali', 'Sénégal']])
        ->assertSessionHasNoErrors()->assertRedirect();

    $zone = Zone::firstOrFail();
    expect($zone->nom)->toBe('Afrique de l\'Ouest')
        ->and($zone->countries()->pluck('nom')->sort()->values()->all())->toBe(['Mali', 'Sénégal']);
});

it('updates a zone and replaces its countries', function () {
    $zone = Zone::factory()->create();
    $zone->countries()->create(['nom' => 'Mali']);

    $this->patch("/admin/zone/{$zone->id}", ['nom' => 'Zone 2', 'pays' => ['Niger', 'Togo']])
        ->assertSessionHasNoErrors()->assertRedirect();

    expect($zone->refresh()->nom)->toBe('Zone 2')
        ->and($zone->countries()->pluck('nom')->sort()->values()->all())->toBe(['Niger', 'Togo']);
});

it('creates a transport and attaches its zones', function () {
    [$a, $b] = Zone::factory()->count(2)->create();

    $this->post('/admin/transport', ['nom' => 'DHL', 'zone_id' => [(string) $a->id, (string) $b->id]])
        ->assertSessionHasNoErrors()->assertRedirect();

    $transport = Transport::firstOrFail();
    expect($transport->nom)->toBe('DHL')
        ->and($transport->zones()->pluck('zones.id')->sort()->values()->all())->toBe([$a->id, $b->id]);
});

it('updates a transport and syncs its zones', function () {
    [$a, $b] = Zone::factory()->count(2)->create();
    $transport = Transport::factory()->create();
    $transport->zones()->attach($a->id);

    $this->patch("/admin/transport/{$transport->id}", ['nom' => 'UPS', 'zone_id' => [(string) $b->id]])
        ->assertSessionHasNoErrors()->assertRedirect();

    expect($transport->refresh()->nom)->toBe('UPS')
        ->and($transport->zones()->pluck('zones.id')->all())->toBe([$b->id]);
});
