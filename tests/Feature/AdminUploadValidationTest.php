<?php

declare(strict_types=1);

use App\Enum\RoleEnum;
use App\Models\Category;
use App\Models\Image;
use App\Models\Product;
use App\Models\Slide;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

// I3 — les uploads admin (slide, image produit) doivent n'accepter que de vraies images

beforeEach(function () {
    Storage::fake('public');
    $this->admin = User::factory()->create(['role' => RoleEnum::ADMIN]);
    $this->slide = Slide::create([
        'text_one' => 'Un',
        'text_two' => 'Deux',
        'paragraph' => 'Texte',
        'image' => 'slide/image/old.jpg',
    ]);
    $this->galleryImage = Image::create([
        'product_id' => Product::factory()->create(['categorie_id' => Category::factory()->create()->id])->id,
        'chemin' => 'product/image/old.jpg',
    ]);
});

function slidePayload(UploadedFile $file): array
{
    return [
        '_method' => 'put',
        'text_one' => 'Un',
        'text_two' => 'Deux',
        'paragraph' => 'Texte',
        'image' => $file,
    ];
}

it('rejects non-image files on slide update and stores nothing', function (string $name, string $mime) {
    $this->actingAs($this->admin)
        ->post("/admin/slide/{$this->slide->id}", slidePayload(UploadedFile::fake()->create($name, 10, $mime)))
        ->assertSessionHasErrors('image');

    expect(Storage::disk('public')->allFiles())->toBeEmpty()
        ->and($this->slide->fresh()->getRawOriginal('image'))->toBe('slide/image/old.jpg');
})->with([
    'php' => ['shell.php', 'application/x-php'],
    'html' => ['page.html', 'text/html'],
    'svg' => ['logo.svg', 'image/svg+xml'],
]);

it('rejects an oversized slide image', function () {
    $this->actingAs($this->admin)
        ->post("/admin/slide/{$this->slide->id}", slidePayload(UploadedFile::fake()->image('big.jpg')->size(6000)))
        ->assertSessionHasErrors('image');

    expect(Storage::disk('public')->allFiles())->toBeEmpty();
});

it('accepts a real image on slide update', function () {
    $this->actingAs($this->admin)
        ->post("/admin/slide/{$this->slide->id}", slidePayload(UploadedFile::fake()->image('new.jpg')))
        ->assertSessionHasNoErrors();

    expect(Storage::disk('public')->allFiles())->toHaveCount(1);
});

it('keeps the slide image when none is sent', function () {
    $this->actingAs($this->admin)
        ->post("/admin/slide/{$this->slide->id}", [
            '_method' => 'put',
            'text_one' => 'Modifié',
            'text_two' => 'Deux',
            'paragraph' => 'Texte',
        ])->assertSessionHasNoErrors();

    expect($this->slide->fresh()->text_one)->toBe('Modifié')
        ->and($this->slide->fresh()->getRawOriginal('image'))->toBe('slide/image/old.jpg');
});

it('rejects non-image files on gallery image update and stores nothing', function (string $name, string $mime) {
    $this->actingAs($this->admin)
        ->post("/admin/image/{$this->galleryImage->id}", [
            '_method' => 'put',
            'chemin' => UploadedFile::fake()->create($name, 10, $mime),
        ])->assertSessionHasErrors('chemin');

    expect(Storage::disk('public')->allFiles())->toBeEmpty()
        ->and($this->galleryImage->fresh()->getRawOriginal('chemin'))->toBe('product/image/old.jpg');
})->with([
    'php' => ['shell.php', 'application/x-php'],
    'html' => ['page.html', 'text/html'],
    'svg' => ['logo.svg', 'image/svg+xml'],
]);

it('accepts a real image on gallery image update', function () {
    $this->actingAs($this->admin)
        ->post("/admin/image/{$this->galleryImage->id}", [
            '_method' => 'put',
            'chemin' => UploadedFile::fake()->image('new.png'),
        ])->assertSessionHasNoErrors();

    expect(Storage::disk('public')->allFiles())->toHaveCount(1);
});

// M5 — les fichiers remplacés ou supprimés disparaissent bien du disque public

it('deletes the slide image file when the slide is deleted', function () {
    Storage::disk('public')->put('slide/image/old.jpg', 'x');

    $this->actingAs($this->admin)->delete("/admin/slide/{$this->slide->id}")->assertSuccessful();

    Storage::disk('public')->assertMissing('slide/image/old.jpg');
    expect(Slide::find($this->slide->id))->toBeNull();
});

it('removes the previous slide image file when it is replaced', function () {
    Storage::disk('public')->put('slide/image/old.jpg', 'x');

    $this->actingAs($this->admin)
        ->post("/admin/slide/{$this->slide->id}", slidePayload(UploadedFile::fake()->image('new.jpg')))
        ->assertSessionHasNoErrors();

    Storage::disk('public')->assertMissing('slide/image/old.jpg');
    expect(Storage::disk('public')->allFiles())->toHaveCount(1);
});

it('deletes the gallery image file when the image is deleted', function () {
    Storage::disk('public')->put('product/image/old.jpg', 'x');

    $this->actingAs($this->admin)->delete("/admin/image/{$this->galleryImage->id}")->assertSuccessful();

    Storage::disk('public')->assertMissing('product/image/old.jpg');
});

it('removes the previous gallery image file when it is replaced', function () {
    Storage::disk('public')->put('product/image/old.jpg', 'x');

    $this->actingAs($this->admin)
        ->post("/admin/image/{$this->galleryImage->id}", [
            '_method' => 'put',
            'chemin' => UploadedFile::fake()->image('new.png'),
        ])->assertSessionHasNoErrors();

    Storage::disk('public')->assertMissing('product/image/old.jpg');
    expect(Storage::disk('public')->allFiles())->toHaveCount(1);
});
