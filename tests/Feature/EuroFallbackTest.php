<?php

declare(strict_types=1);

use App\Models\Order;
use App\Models\Shipping;

function orderWithShipping(int $montant): Order
{
    return (new Order)->forceFill(['shipping' => $montant]);
}

function shippingWithMontant(int $montant): Shipping
{
    return (new Shipping)->forceFill(['montant' => $montant]);
}

it('converts shipping to EUR when a valid rate exists', function () {
    session(['devise' => 'EUR', 'taux_eur' => 655.957]);

    expect(orderWithShipping(15000)->getShipping())->toBe('22.87')
        ->and(orderWithShipping(15000)->getTaux())->toBe(655.957)
        ->and(shippingWithMontant(15000)->montant_devise)->toBe(22.87);
});

it('keeps CFA shipping when the currency is CFA', function () {
    session(['devise' => 'CFA', 'taux_eur' => 655.957]);

    expect(orderWithShipping(15000)->getShipping())->toBe(15000)
        ->and(shippingWithMontant(15000)->montant_devise)->toBe(15000.0);
});

it('falls back to CFA for Order and Shipping when the EUR rate is missing or invalid', function (mixed $taux) {
    session(['devise' => 'EUR', 'taux_eur' => $taux]);

    expect(orderWithShipping(15000)->getShipping())->toBe(15000)
        ->and(orderWithShipping(15000)->getTaux())->toBeNull()
        ->and(shippingWithMontant(15000)->montant_devise)->toBe(15000.0);
})->with([null, 0, '0', 'abc']);
