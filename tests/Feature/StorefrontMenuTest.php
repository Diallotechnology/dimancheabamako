<?php

declare(strict_types=1);

use App\Models\Category;
use Illuminate\Support\Facades\Blade;

it('shows categories in the storefront layout', function () {
    Category::factory()->create(['nom' => 'Bazin Riche']);

    $this->withSession(['devise' => 'CFA', 'taux_eur' => 655.957])->get('/')->assertOk()->assertSee('Bazin Riche');
});

it('renders the redesigned shop layout with its header, footer and cart counter', function () {
    $this->withoutVite();
    Category::factory()->create(['nom' => 'Bazin Riche']);
    session(['devise' => 'CFA', 'taux_eur' => 655.957]);

    $html = Blade::render('<x-shop-layout><x-slot:title>Essai</x-slot:title><p>Contenu</p></x-shop-layout>');

    expect($html)
        ->toContain('<title>Essai · Dimanche à Bamako</title>')
        ->toContain('Bazin Riche')
        ->toContain('Contenu')
        ->toContain(route('change_devise', 'EUR'))
        ->toContain(route('change_language', 'en'))
        ->toContain(route('panier'))
        ->toContain('id="contenu"')
        ->not->toContain('main.css')
        ->not->toContain('bootstrap');
});
