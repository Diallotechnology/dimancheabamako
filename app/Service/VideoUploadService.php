<?php

declare(strict_types=1);

namespace App\Service;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

final class VideoUploadService
{
    /**
     * Stocke la vidéo sur le disque public et retourne son chemin relatif (ex: product/video/<hash>.mp4).
     * Lue en flux depuis le chemin reçu par PHP, sans `getRealPath()` (voir ImageUploadService::store).
     */
    public function store(UploadedFile $file, string $directory): string
    {
        $stream = fopen($file->getPathname(), 'rb');

        if ($stream === false) {
            throw new RuntimeException('Impossible de lire la vidéo envoyée.');
        }

        try {
            $path = mb_trim($directory, '/').'/'.$file->hashName();
            Storage::disk('public')->put($path, $stream);
        } finally {
            fclose($stream);
        }

        return $path;
    }
}
