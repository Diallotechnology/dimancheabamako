<?php

declare(strict_types=1);

namespace App\Service;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Format;
use Intervention\Image\ImageManager;

final readonly class ImageUploadService
{
    public const int MAX_WIDTH = 1200;

    public const int QUALITY = 75;

    public function __construct(private ImageManager $manager) {}

    /**
     * Redimensionne (sans agrandir), convertit en WebP et stocke sur le disque public.
     * Retourne le chemin relatif (ex: product/cover/<uuid>.webp).
     */
    public function store(UploadedFile $file, string $directory): string
    {
        $image = $this->manager
            ->decodePath($file->getRealPath())
            ->scaleDown(width: self::MAX_WIDTH);

        $path = mb_trim($directory, '/').'/'.Str::uuid()->toString().'.webp';

        Storage::disk('public')->put($path, (string) $image->encodeUsingFormat(Format::WEBP, quality: self::QUALITY));

        return $path;
    }
}
