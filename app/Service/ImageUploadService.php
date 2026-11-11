<?php

declare(strict_types=1);

namespace App\Service;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Format;
use Intervention\Image\ImageManager;
use Intervention\Image\Interfaces\ImageInterface;

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
        // Le contenu est lu depuis le chemin tel que PHP l'a reçu, sans `getRealPath()` : celui-ci
        // échoue quand le dossier temporaire d'upload n'est pas lisible pour le serveur web (Windows).
        return $this->storeFromContent($file->get(), $directory);
    }

    /**
     * Même traitement à partir d'un fichier local (import en masse).
     */
    public function storeFromPath(string $path, string $directory): string
    {
        return $this->storeDecoded($this->manager->decodePath($path), $directory);
    }

    private function storeFromContent(string $content, string $directory): string
    {
        return $this->storeDecoded($this->manager->decode($content), $directory);
    }

    private function storeDecoded(ImageInterface $decoded, string $directory): string
    {
        $image = $decoded->scaleDown(width: self::MAX_WIDTH);

        $stored = mb_trim($directory, '/').'/'.Str::uuid()->toString().'.webp';

        Storage::disk('public')->put($stored, (string) $image->encodeUsingFormat(Format::WEBP, quality: self::QUALITY));

        return $stored;
    }
}
