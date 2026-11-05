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
        return $this->storeFromPath((string) $file->getRealPath(), $directory);
    }

    /**
     * Même traitement à partir d'un fichier local (import en masse).
     */
    public function storeFromPath(string $path, string $directory): string
    {
        $image = $this->manager
            ->decodePath($path)
            ->scaleDown(width: self::MAX_WIDTH);

        $stored = mb_trim($directory, '/').'/'.Str::uuid()->toString().'.webp';

        Storage::disk('public')->put($stored, (string) $image->encodeUsingFormat(Format::WEBP, quality: self::QUALITY));

        return $stored;
    }
}
