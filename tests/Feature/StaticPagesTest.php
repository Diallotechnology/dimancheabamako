<?php

declare(strict_types=1);

beforeEach(function () {
    session(['devise' => 'CFA', 'taux_eur' => 655.957]);
});

it('serves the editorial pages on the shop layout', function (string $route, string $title) {
    $this->withoutVite();

    $this->get(route($route))
        ->assertOk()
        ->assertSee('id="contenu"', false)
        ->assertSee('<h1', false)
        ->assertSee($title)
        ->assertDontSee('data-bs-', false);
})->with([
    'about' => ['about', 'À propos'],
    'livraison' => ['livraison', 'Livraison'],
    'contact' => ['contact', 'Contactez-nous'],
]);

it('keeps the contact form posting every field to the mail route', function () {
    $this->withoutVite();

    $html = $this->get(route('contact'))->assertOk()->getContent();

    foreach (['name', 'email', 'subject', 'message'] as $field) {
        expect($html)->toContain('name="'.$field.'"');
    }
    expect($html)->toContain('action="'.route('contact.email').'"');
});
