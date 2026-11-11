<?php

declare(strict_types=1);

use App\Enum\RoleEnum;
use App\Models\Slide;
use App\Models\User;
use App\Service\ImageUploadService;
use App\Service\VideoUploadService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(fn () => Storage::fake('public'));

it('stores a compressed webp smaller than the original', function () {
    $file = UploadedFile::fake()->image('photo.png', 2400, 1600);

    $path = app(ImageUploadService::class)->store($file, 'product/cover');

    expect($path)->toStartWith('product/cover/')->toEndWith('.webp');
    Storage::disk('public')->assertExists($path);

    $stored = Storage::disk('public')->get($path);
    expect(mb_strlen($stored, '8bit'))->toBeLessThan($file->getSize())
        ->and(getimagesizefromstring($stored))->toMatchArray([0 => 1200, 1 => 800, 'mime' => 'image/webp']);
});

it('never upscales small images', function () {
    $path = app(ImageUploadService::class)->store(UploadedFile::fake()->image('s.jpg', 300, 200), 'product/image');

    [$w, $h] = getimagesizefromstring(Storage::disk('public')->get($path));

    expect([$w, $h])->toBe([300, 200]);
});

it('compresses the slide image on admin update', function () {
    $admin = User::factory()->create(['role' => RoleEnum::ADMIN]);
    $slide = Slide::create(['text_one' => 'Un', 'text_two' => 'Deux', 'paragraph' => 'T', 'image' => 'slide/image/old.jpg']);

    $this->actingAs($admin)->post("/admin/slide/{$slide->id}", [
        '_method' => 'put',
        'text_one' => 'Un',
        'text_two' => 'Deux',
        'paragraph' => 'T',
        'image' => UploadedFile::fake()->image('s.jpg', 2000, 1000),
    ])->assertRedirect();

    expect($slide->fresh()->getRawOriginal('image'))->toEndWith('.webp');
});

/**
 * Reproduit le bug local : le fichier uploadé existe et se lit, mais `getRealPath()` échoue
 * (dossier temporaire de PHP illisible pour le compte du serveur web, ex. C:\Windows\Temp).
 */
function uploadWithoutRealPath(UploadedFile $source): UploadedFile
{
    // Copie dans un fichier propre : le temporaire de `fake()` est supprimé dès que `$source` est libéré.
    $path = tempnam(sys_get_temp_dir(), 'upl');
    copy($source->getPathname(), $path);
    register_shutdown_function(static fn () => @unlink($path));

    return new class($path, $source->getClientOriginalName(), $source->getMimeType(), null, true) extends UploadedFile
    {
        public function getRealPath(): string|false
        {
            return false;
        }
    };
}

it('stores an image even when the uploaded file has no resolvable real path', function () {
    $file = uploadWithoutRealPath(UploadedFile::fake()->image('photo.png', 800, 600));

    $path = app(ImageUploadService::class)->store($file, 'product/cover');

    Storage::disk('public')->assertExists($path);
    expect(getimagesizefromstring(Storage::disk('public')->get($path)))->toMatchArray([0 => 800, 1 => 600, 'mime' => 'image/webp']);
});

it('stores a video even when the uploaded file has no resolvable real path', function () {
    $file = uploadWithoutRealPath(UploadedFile::fake()->create('clip.mp4', 100, 'video/mp4'));

    $path = app(VideoUploadService::class)->store($file, 'product/video');

    expect($path)->toStartWith('product/video/');
    Storage::disk('public')->assertExists($path);
    expect(Storage::disk('public')->size($path))->toBe($file->getSize());
});
