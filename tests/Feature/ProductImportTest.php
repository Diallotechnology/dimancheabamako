<?php

declare(strict_types=1);

use App\Enum\ProductStatus;
use App\Enum\RoleEnum;
use App\Jobs\ProcessProductImageJob;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('local');
    $this->secretary = User::factory()->create(['role' => RoleEnum::SECRTETAIRE]);
});

it('keeps the import routes behind the secretary role', function () {
    $this->get('/admin/product/import')->assertRedirect();

    $customer = User::factory()->create(['role' => RoleEnum::CUSTOMER]);
    $this->actingAs($customer)->get('/admin/product/import')->assertForbidden();
    $this->actingAs($customer)
        ->post('/admin/product/import', ['images' => [UploadedFile::fake()->image('a.jpg')]])
        ->assertForbidden();

    $this->actingAs($this->secretary)->get('/admin/product/import')->assertOk();
});

it('rejects invalid uploads without dispatching anything', function (array $images) {
    Bus::fake();

    $this->actingAs($this->secretary)
        ->postJson('/admin/product/import', ['images' => $images])
        ->assertUnprocessable();

    Bus::assertNothingBatched();
})->with([
    'aucune image' => [[]],
    'pas une image' => fn () => [[UploadedFile::fake()->create('doc.pdf', 10, 'application/pdf')]],
    'trop lourde' => fn () => [[UploadedFile::fake()->image('big.jpg')->size(6000)]],
    'trop nombreuses' => fn () => [array_map(fn (int $i) => UploadedFile::fake()->image("p$i.jpg"), range(1, 21))],
]);

it('dispatches one batched job per photo with the categories loaded once', function () {
    Bus::fake();
    $category = Category::factory()->create(['nom' => 'Bazin']);

    $response = $this->actingAs($this->secretary)->postJson('/admin/product/import', [
        'images' => [UploadedFile::fake()->image('a.jpg'), UploadedFile::fake()->image('b.png')],
    ]);

    $response->assertStatus(202)->assertJson(['total' => 2]);

    Bus::assertBatched(function ($batch) use ($category) {
        return $batch->jobs->count() === 2
            && $batch->connection() === 'database'
            && $batch->jobs->every(
                fn ($job) => $job instanceof ProcessProductImageJob
                    && $job->categories === [$category->id => 'Bazin']
                    && is_file($job->imagePath)
            );
    });
});

it('stores imported photos even when the uploaded file has no resolvable real path', function () {
    Bus::fake();

    $response = $this->actingAs($this->secretary)->call(
        'POST',
        '/admin/product/import',
        [],
        [],
        ['images' => [uploadWithoutRealPath(UploadedFile::fake()->image('a.jpg'))]],
        ['HTTP_ACCEPT' => 'application/json'],
    );

    $response->assertStatus(202)->assertJson(['total' => 1]);

    Bus::assertBatched(fn ($batch) => $batch->jobs->count() === 1 && is_file($batch->jobs->first()->imagePath));
});

it('reports batch progress and 404s on an unknown batch', function () {
    $this->actingAs($this->secretary)
        ->getJson('/admin/product/import/'.fake()->uuid())
        ->assertNotFound();

    $batch = Bus::batch([])->name('t')->dispatch();

    $this->actingAs($this->secretary)
        ->getJson('/admin/product/import/'.$batch->id)
        ->assertOk()
        ->assertJsonStructure(['total', 'processed', 'failed', 'finished', 'progress']);
});

// Avec allowFailures, les jobs en échec restent dans pending_jobs et finished_at n'est jamais renseigné :
// le lot est terminé quand il ne reste plus de job en attente de succès (pending == failed).
it('reports a batch as finished once only failed jobs remain pending', function (int $total, int $pending, int $failed, bool $finished, int $processed, int $progress) {
    $batch = Bus::batch([])->name('t')->dispatch();
    DB::table('job_batches')->where('id', $batch->id)->update([
        'total_jobs' => $total,
        'pending_jobs' => $pending,
        'failed_jobs' => $failed,
        'finished_at' => null,
    ]);

    $this->actingAs($this->secretary)
        ->getJson('/admin/product/import/'.$batch->id)
        ->assertOk()
        ->assertJson([
            'total' => $total,
            'failed' => $failed,
            'processed' => $processed,
            'finished' => $finished,
            'progress' => $progress,
        ]);
})->with([
    'tous en échec' => [3, 3, 3, true, 0, 100],
    '2 réussis, 1 en échec' => [3, 1, 1, true, 2, 100],
    'un échec, il en reste à traiter' => [3, 2, 1, false, 1, 67],
    'rien traité' => [3, 3, 0, false, 0, 0],
]);

it('filters the product list on draft status', function () {
    $category = Category::factory()->create();
    Product::factory()->create(['categorie_id' => $category->id, 'status' => ProductStatus::Draft, 'nom' => 'Brouillon A']);
    Product::factory()->create(['categorie_id' => $category->id, 'status' => ProductStatus::Published, 'nom' => 'Publié B']);

    $this->actingAs($this->secretary)
        ->get('/admin/product?statut=draft')
        ->assertInertia(fn ($page) => $page->has('rows.data', 1)->where('rows.data.0.nom', 'Brouillon A'));

    // Valeur inconnue (vieux lien) : filtre ignoré, pas de liste vide.
    $this->actingAs($this->secretary)
        ->get('/admin/product?statut=xyz')
        ->assertInertia(fn ($page) => $page->has('rows.data', 2));
});

it('lists only the products of the requested batch on the review page', function () {
    $category = Category::factory()->create();
    $batch = Bus::batch([])->name('t')->dispatch();
    $other = Bus::batch([])->name('t2')->dispatch();

    Product::factory()->create(['categorie_id' => $category->id, 'status' => ProductStatus::Draft, 'batch_id' => $batch->id, 'nom' => 'Du batch']);
    Product::factory()->create(['categorie_id' => $category->id, 'status' => ProductStatus::Draft, 'batch_id' => $other->id, 'nom' => 'Autre batch']);
    Product::factory()->create(['categorie_id' => $category->id, 'status' => ProductStatus::Draft, 'batch_id' => null, 'nom' => 'Ancien brouillon']);

    $this->actingAs($this->secretary)
        ->get('/admin/product/import/'.$batch->id.'/review')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Admin/Product/ImportReview')
            ->has('products', 1)
            ->where('products.0.nom', 'Du batch')
            ->has('category', 1));

    $this->actingAs($this->secretary)
        ->get('/admin/product/import/'.fake()->uuid().'/review')
        ->assertNotFound();

    $customer = User::factory()->create(['role' => RoleEnum::CUSTOMER]);
    $this->actingAs($customer)->get('/admin/product/import/'.$batch->id.'/review')->assertForbidden();
});

it('publishes a reviewed draft through the normal update rules and rejects invalid input', function () {
    $category = Category::factory()->create();
    $product = Product::factory()->create([
        'categorie_id' => $category->id,
        'status' => ProductStatus::Draft,
        'prix' => 0,
        'poids' => 0,
        'stock' => 0,
    ]);

    $payload = [
        'categorie_id' => $category->id,
        'reference' => $product->reference,
        'nom' => $product->nom,
        'color' => 'bleu',
        'taille' => 'L',
        'resume' => 'Résumé relu',
        'poids' => '1.5',
        'favoris' => 0,
        'is_preorder' => 0,
        'prix' => 25000,
        'stock' => 4,
        'stock_initial' => 0,
        'status' => 'published',
    ];

    // Mêmes règles que UpdateProductRequest : prix négatif, statut inconnu (la couleur est facultative).
    $this->actingAs($this->secretary)
        ->put("/admin/product/{$product->id}", array_merge($payload, ['prix' => -1, 'status' => 'nope']))
        ->assertSessionHasErrors(['prix', 'status']);
    expect($product->fresh()->status)->toBe(ProductStatus::Draft);

    $this->actingAs($this->secretary)
        ->put("/admin/product/{$product->id}", $payload)
        ->assertSessionHasNoErrors();

    $fresh = $product->fresh();
    expect($fresh->status)->toBe(ProductStatus::Published)
        ->and($fresh->prix)->toBe(25000)
        ->and($fresh->stock)->toBe(4);
});
