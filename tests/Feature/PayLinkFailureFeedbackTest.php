<?php

declare(strict_types=1);

use App\Enum\RoleEnum;
use App\Models\PayLink;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    $this->actingAs(User::factory()->create(['role' => RoleEnum::ADMIN]));
    Cache::put('ngenius_access_token', 'tok', 600);
});

function payLink(int $montant = 1000): PayLink
{
    return PayLink::create([
        'name' => 'Awa',
        'contact' => '70000000',
        'montant' => $montant,
        'lien' => 'https://pay.example/old',
        'trans_ref' => 'old-ref',
        'etat' => 'Pending',
    ]);
}

function ngeniusRejects(): void
{
    Http::fake(['*' => Http::response(['message' => 'boom'], 500)]);
}

function ngeniusAnswersWithoutLink(): void
{
    Http::fake(['*' => Http::response(['reference' => 'new-ref'], 201)]);
}

it('returns a validation error to Inertia when store fails at N-Genius', function () {
    ngeniusRejects();

    $this->from('/admin/paylink')
        ->post('/admin/paylink', ['name' => 'Awa', 'contact' => '70000000', 'montant' => 2500])
        ->assertRedirect('/admin/paylink')
        ->assertSessionHasErrors('ngenius');

    expect(PayLink::count())->toBe(0);
});

it('returns a validation error to Inertia when regenerate fails at N-Genius', function () {
    $paylink = payLink();
    ngeniusRejects();

    $this->from('/admin/paylink')
        ->post("/admin/paylink/{$paylink->id}/regenerate")
        ->assertRedirect('/admin/paylink')
        ->assertSessionHasErrors('ngenius');

    expect($paylink->fresh()->lien)->toBe('https://pay.example/old');
});

it('reports an error and keeps the old amount when update cannot get a new link (HTTP failure)', function () {
    $paylink = payLink(1000);
    ngeniusRejects();

    $this->from("/admin/paylink/{$paylink->id}/edit")
        ->put("/admin/paylink/{$paylink->id}", ['name' => 'Awa', 'contact' => '70000000', 'montant' => 5000])
        ->assertSessionHasErrors('ngenius');

    $fresh = $paylink->fresh();
    expect($fresh->montant)->toBe(1000)
        ->and($fresh->lien)->toBe('https://pay.example/old');
});

it('reports an error and keeps the old amount when N-Genius answers without payment link', function () {
    $paylink = payLink(1000);
    ngeniusAnswersWithoutLink();

    $this->from("/admin/paylink/{$paylink->id}/edit")
        ->put("/admin/paylink/{$paylink->id}", ['name' => 'Awa', 'contact' => '70000000', 'montant' => 5000])
        ->assertSessionHasErrors('ngenius');

    expect($paylink->fresh()->montant)->toBe(1000);
});

it('updates the amount and the link together when N-Genius confirms', function () {
    $paylink = payLink(1000);
    Http::fake(['*' => Http::response([
        'reference' => 'new-ref',
        '_links' => ['payment' => ['href' => 'https://pay.example/new']],
    ], 201)]);

    $this->put("/admin/paylink/{$paylink->id}", ['name' => 'Awa', 'contact' => '70000000', 'montant' => 5000])
        ->assertSessionHasNoErrors();

    $fresh = $paylink->fresh();
    expect($fresh->montant)->toBe(5000)
        ->and($fresh->lien)->toBe('https://pay.example/new')
        ->and($fresh->trans_ref)->toBe('new-ref');
});

it('does not call N-Genius when update leaves the amount unchanged', function () {
    $paylink = payLink(1000);
    Http::fake();

    $this->put("/admin/paylink/{$paylink->id}", ['name' => 'Bintou', 'contact' => '70000000', 'montant' => 1000])
        ->assertSessionHasNoErrors();

    Http::assertNothingSent();
    expect($paylink->fresh()->name)->toBe('Bintou');
});
