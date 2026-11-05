<?php

declare(strict_types=1);

use App\Enum\RoleEnum;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('public');
    $this->secretary = User::factory()->create(['role' => RoleEnum::SECRTETAIRE]);
    $this->category = Category::factory()->create();
});

function productPayload(array $overrides = []): array
{
    return array_merge([
        'categorie_id' => test()->category->id,
        'reference' => 'REF-001',
        'nom' => 'Bazin riche brodé',
        'color' => 'Bleu',
        'taille' => 'L',
        'resume' => 'Un beau bazin.',
        'description' => 'Description longue.',
        'poids' => '1.5',
        'prix' => 45000,
        'stock' => 5,
        'favoris' => 0,
        'is_preorder' => 0,
        'cover' => UploadedFile::fake()->image('cover.jpg'),
        'image' => [UploadedFile::fake()->image('a.jpg'), UploadedFile::fake()->image('b.jpg')],
    ], $overrides);
}

it('creates a product with cover and gallery images', function () {
    $this->actingAs($this->secretary)
        ->post('/admin/product', productPayload())
        ->assertSessionHasNoErrors();

    $product = Product::where('reference', 'REF-001')->firstOrFail();

    expect($product->slug)->toBe('bazin-riche-brode')
        ->and($product->images)->toHaveCount(2);
    Storage::disk('public')->assertExists($product->getRawOriginal('cover'));
});

it('rejects duplicate references', function () {
    Product::factory()->create(['categorie_id' => $this->category->id, 'reference' => 'REF-001']);

    $this->actingAs($this->secretary)
        ->post('/admin/product', productPayload())
        ->assertSessionHasErrors('reference');
});

it('generates unique slugs for products with the same name', function () {
    $this->actingAs($this->secretary)->post('/admin/product', productPayload());
    $this->actingAs($this->secretary)->post('/admin/product', productPayload(['reference' => 'REF-002']));

    expect(Product::pluck('slug')->all())->toBe(['bazin-riche-brode', 'bazin-riche-brode-1']);
});

it('refuses non-image files as cover or gallery image', function () {
    $this->actingAs($this->secretary)
        ->post('/admin/product', productPayload([
            'cover' => UploadedFile::fake()->create('shell.php', 10, 'application/x-php'),
            'image' => [UploadedFile::fake()->create('shell.php', 10, 'application/x-php')],
        ]))
        ->assertSessionHasErrors(['cover', 'image.0']);

    expect(Product::count())->toBe(0);
});

it('refuses non-video files as video', function () {
    $this->actingAs($this->secretary)
        ->post('/admin/product', productPayload([
            'video' => UploadedFile::fake()->create('shell.php', 10, 'application/x-php'),
        ]))
        ->assertSessionHasErrors('video');
});

it('forbids a customer from creating a product', function () {
    $customer = User::factory()->create(['role' => RoleEnum::CUSTOMER]);

    $this->actingAs($customer)->post('/admin/product', productPayload())->assertForbidden();

    expect(Product::count())->toBe(0);
});

function updatePayload(Product $product, array $overrides = []): array
{
    return array_merge([
        'categorie_id' => $product->categorie_id,
        'reference' => $product->reference,
        'nom' => $product->nom,
        'color' => 'Bleu',
        'taille' => 'L',
        'resume' => 'x',
        'poids' => '1',
        'prix' => 1000,
        'stock' => $product->stock,
        'stock_initial' => $product->stock,
        'favoris' => 0,
        'is_preorder' => 0,
    ], $overrides);
}

it('keeps the cover file on disk when only a video is uploaded on update', function () {
    $cover = 'product/cover/keep.jpg';
    Storage::disk('public')->put($cover, 'cover-bytes');
    $product = Product::factory()->create(['categorie_id' => $this->category->id, 'cover' => $cover]);

    $this->actingAs($this->secretary)
        ->put("/admin/product/{$product->id}", updatePayload($product, [
            'video' => UploadedFile::fake()->create('clip.mp4', 100, 'video/mp4'),
        ]))
        ->assertSessionHasNoErrors();

    Storage::disk('public')->assertExists($cover);
    expect($product->fresh()->getRawOriginal('video'))->not->toBeNull();
});

it('replaces the cover and removes the old file when a new cover is uploaded', function () {
    $old = 'product/cover/old.jpg';
    Storage::disk('public')->put($old, 'old');
    $product = Product::factory()->create(['categorie_id' => $this->category->id, 'cover' => $old]);

    $this->actingAs($this->secretary)
        ->put("/admin/product/{$product->id}", updatePayload($product, [
            'cover' => UploadedFile::fake()->image('new.jpg'),
        ]))
        ->assertSessionHasNoErrors();

    Storage::disk('public')->assertMissing($old);
    Storage::disk('public')->assertExists($product->fresh()->getRawOriginal('cover'));
});

it('replaces the video and removes the old video file', function () {
    $oldVideo = 'product/video/old.mp4';
    Storage::disk('public')->put($oldVideo, 'old');
    $product = Product::factory()->create(['categorie_id' => $this->category->id, 'video' => $oldVideo]);

    $this->actingAs($this->secretary)
        ->put("/admin/product/{$product->id}", updatePayload($product, [
            'video' => UploadedFile::fake()->create('new.mp4', 100, 'video/mp4'),
        ]))
        ->assertSessionHasNoErrors();

    Storage::disk('public')->assertMissing($oldVideo);
});

it('deletes cover, video and gallery files when a product is destroyed', function () {
    $paths = ['product/cover/c.jpg', 'product/video/v.mp4', 'product/image/g1.jpg', 'product/image/g2.jpg'];
    foreach ($paths as $path) {
        Storage::disk('public')->put($path, 'x');
    }
    $product = Product::factory()->create([
        'categorie_id' => $this->category->id,
        'cover' => $paths[0],
        'video' => $paths[1],
    ]);
    $product->images()->createMany([['chemin' => $paths[2]], ['chemin' => $paths[3]]]);

    $this->actingAs($this->secretary)->delete("/admin/product/{$product->id}")->assertOk();

    foreach ($paths as $path) {
        Storage::disk('public')->assertMissing($path);
    }
    expect(Product::find($product->id))->toBeNull();
});
