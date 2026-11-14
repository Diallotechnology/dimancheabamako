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

it('serves the authentication pages on the shop layout', function (string $uri) {
    $this->withoutVite();

    $this->get($uri)
        ->assertOk()
        ->assertSee('id="contenu"', false)
        ->assertSee('<h1', false)
        ->assertDontSee('form-control', false)
        ->assertDontSee('card-login', false);
})->with(['/login', '/register', '/forgot-password', '/reset-password/jeton-de-test']);

it('serves the profile page to a signed-in customer on the shop layout', function () {
    $this->withoutVite();
    // Relu en base : un modèle tout juste créé n'a pas `client_id`, que le profil lit.
    $user = App\Models\User::findOrFail(App\Models\User::factory()->create(['role' => App\Enum\RoleEnum::CUSTOMER])->id);

    $this->actingAs($user)
        ->get(route('profil'))
        ->assertOk()
        ->assertSee('id="contenu"', false)
        ->assertSee('name="current_password"', false)
        ->assertSee('action="'.route('profil.update', $user).'"', false);
});
