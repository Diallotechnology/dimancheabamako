<?php

declare(strict_types=1);

// M6 — les routes publiques coûteuses ou sensibles sont limitées en débit

it('throttles order creation', function () {
    foreach (range(1, 10) as $_) {
        $this->post('/order', [])->assertStatus(302);
    }

    $this->post('/order', [])->assertStatus(429);
});

it('throttles invoice lookups', function () {
    foreach (range(1, 30) as $_) {
        $this->get('/order/invoice/unknown-ref')->assertNotFound();
    }

    $this->get('/order/invoice/unknown-ref')->assertStatus(429);
});
