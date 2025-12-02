<?php

declare(strict_types=1);

namespace App\Service;

use App\Models\Category;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

final class CategoryService
{
    private const CACHE_KEY = 'categories_list';

    private const LOCK_KEY = 'categories_rebuild';

    private const LOCK_TTL = 5; // secondes

    /**
     * Récupère toutes les catégories avec cache + lock sur la reconstruction.
     */
    public function all(): Collection
    {
        // Si déjà en cache → on renvoie direct
        if (Cache::has(self::CACHE_KEY)) {
            return Cache::get(self::CACHE_KEY);
        }

        // Sinon on protège la reconstruction avec un lock
        return Cache::lock(self::LOCK_KEY, self::LOCK_TTL)->block(self::LOCK_TTL, function () {
            // Double-check après avoir obtenu le lock (un autre process a pu reconstruire entre-temps)
            if (Cache::has(self::CACHE_KEY)) {
                return Cache::get(self::CACHE_KEY);
            }

            $categories = Category::select('id', 'nom')
                ->orderBy('nom')
                ->get();

            Cache::forever(self::CACHE_KEY, $categories);

            return $categories;
        });
    }

    /**
     * Invalidation explicite du cache.
     */
    public function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }
}
