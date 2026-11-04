<?php

declare(strict_types=1);

use App\Models\Category;

it('shows categories in the storefront layout', function () {
    Category::factory()->create(['nom' => 'Bazin Riche']);

    $this->withSession(['devise' => 'CFA', 'taux_eur' => 655.957])->get('/')->assertOk()->assertSee('Bazin Riche');
});
