<?php

declare(strict_types=1);

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

beforeEach(function () {
    $this->withoutVite();
});

it('renders every error code with the standalone page, without Evara', function (int $status, string $title) {
    Route::get("/__erreur/$status", fn () => abort($status));

    $this->get("/__erreur/$status")
        ->assertStatus($status)
        ->assertSee((string) $status)
        ->assertSee($title)
        ->assertDontSee('assets/css/main.css', false)
        ->assertDontSee('jquery', false);
})->with([
    [401, 'Connexion requise'],
    [403, 'Accès refusé'],
    [404, 'Page introuvable'],
    [419, 'Page expirée'],
    [429, 'Trop de requêtes'],
    [500, 'Erreur du serveur'],
    [503, 'Service temporairement indisponible'],
    [418, 'Une erreur est survenue'], // code sans texte dédié : repli générique
]);

it('does not query the database to render an error page, so a database failure cannot break it', function () {
    Route::get('/__erreur/500', fn () => abort(500));

    DB::enableQueryLog();
    $this->get('/__erreur/500')->assertStatus(500);

    expect(array_column(DB::getQueryLog(), 'query'))->toBe([]);
});

it('offers a way back home except on maintenance', function () {
    Route::get('/__erreur/404', fn () => abort(404));
    Route::get('/__erreur/503', fn () => abort(503));

    $this->get('/__erreur/404')->assertSee(route('home'), false);
    $this->get('/__erreur/503')->assertDontSee(__('messages.back_to_home'));
});
