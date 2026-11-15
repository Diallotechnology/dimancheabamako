<?php

declare(strict_types=1);

use App\Enum\RoleEnum;
use App\Models\Category;
use App\Models\Image;
use App\Models\Product;
use App\Models\Slide;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;

// Remplacer une image : le nouveau fichier est stocké AVANT de supprimer l'ancien.

beforeEach(function () {
    Storage::fake('public');
    $this->admin = User::factory()->create(['role' => RoleEnum::ADMIN]);
    Storage::disk('public')->put('slide/image/old.jpg', 'ancien-slide');
    Storage::disk('public')->put('product/image/old.jpg', 'ancienne-image');
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
    // Image valide pour la validation, mais illisible pour le traitement (échec de store()).
    $this->app->instance(ImageManager::class, Mockery::mock(ImageManager::class, function ($mock): void {
        $mock->shouldReceive('decode')->andThrow(new RuntimeException('décodage impossible'));
    }));
    $this->withoutExceptionHandling();
});

it('keeps the old slide file and row when storing the new one fails', function () {
    $post = fn () => $this->actingAs($this->admin)->post("/admin/slide/{$this->slide->id}", [
        '_method' => 'put',
        'text_one' => 'Un',
        'text_two' => 'Deux',
        'paragraph' => 'Texte',
        'image' => UploadedFile::fake()->image('new.jpg'),
    ]);

    expect($post)->toThrow(RuntimeException::class);

    Storage::disk('public')->assertExists('slide/image/old.jpg');
    expect($this->slide->fresh()->getRawOriginal('image'))->toBe('slide/image/old.jpg');
});

it('keeps the old gallery file and row when storing the new one fails', function () {
    $post = fn () => $this->actingAs($this->admin)->post("/admin/image/{$this->galleryImage->id}", [
        '_method' => 'put',
        'chemin' => UploadedFile::fake()->image('new.png'),
    ]);

    expect($post)->toThrow(RuntimeException::class);

    Storage::disk('public')->assertExists('product/image/old.jpg');
    expect($this->galleryImage->fresh()->getRawOriginal('chemin'))->toBe('product/image/old.jpg');
});

it('only exposes the slide and image routes that have a controller method', function () {
    $actions = collect(Route::getRoutes()->getRoutes())
        ->filter(fn ($route) => str_starts_with($route->getName() ?? '', 'slide.') || str_starts_with($route->getName() ?? '', 'image.'))
        ->map(fn ($route) => $route->getActionName())
        ->unique();

    foreach ($actions as $action) {
        [$class, $method] = explode('@', $action);
        expect(method_exists($class, $method))->toBeTrue($action);
    }

    expect(Route::has('slide.store'))->toBeFalse()
        ->and(Route::has('slide.show'))->toBeFalse();
});
