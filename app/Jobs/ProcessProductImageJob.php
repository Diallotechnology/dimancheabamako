<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Enum\ProductStatus;
use App\Models\Category;
use App\Models\Product;
use App\Service\Ai\ProductDescriptionGenerator;
use App\Service\Ai\ProductDraftData;
use App\Service\Ai\ProductDraftPrompt;
use App\Service\ImageUploadService;
use App\Service\ProductReferenceGenerator;
use Illuminate\Bus\Batchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;

/**
 * Crée un produit en brouillon à partir d'une photo (fiche générée par l'IA).
 * Compatible Bus::batch([...]) ; `$importId` identifie l'import (distinct du batchId de Batchable).
 */
final class ProcessProductImageJob implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable;

    public const int CACHE_TTL_DAYS = 30;

    // Pas de retry aveugle : le quota gratuit Gemini est limité, l'échec se relance depuis le batch.
    public int $tries = 1;

    /**
     * @param  array<int, string>|null  $categories  id => nom ; à fournir une fois pour tout le batch
     *                                               (sinon chargé ici, une requête par job)
     */
    public function __construct(
        public readonly string $imagePath,
        public readonly string $importId,
        public readonly ?array $categories = null,
    ) {}

    public function handle(
        ProductDescriptionGenerator $generator,
        ProductReferenceGenerator $references,
        ImageUploadService $images,
    ): void {
        if ($this->batch()?->cancelled()) {
            return;
        }

        $hash = sha1_file($this->imagePath);
        if ($hash === false) {
            throw new RuntimeException('Image illisible.');
        }

        $categories = $this->categories ?? Category::query()->orderBy('nom')->pluck('nom', 'id')->all();

        // La clé dépend du prompt complet (catégories proposées + formulation des consignes) :
        // changer la liste ou le texte de la consigne ne ressort jamais d'anciennes fiches du cache.
        $key = 'ai:product-draft:'.$hash.':'.md5(ProductDraftPrompt::for($categories));

        // L'IA est appelée avant toute écriture : un échec ne laisse ni produit ni fichier.
        // On ne met en cache que le tableau du DTO, et seulement après un succès (remember ne stocke pas une exception).
        /** @var array<string, mixed> $draftData */
        $draftData = Cache::remember(
            $key,
            now()->addDays(self::CACHE_TTL_DAYS),
            fn (): array => $generator->generate($this->imagePath, $categories)->toArray(),
        );
        $draft = ProductDraftData::fromArray($draftData);

        $cover = $images->storeFromPath($this->imagePath, 'product/cover');

        try {
            Product::create([
                'categorie_id' => $this->resolveCategoryId($draft->categorieSuggeree, $categories),
                'reference' => $references->generate(),
                'nom' => $draft->titre,
                'resume' => $draft->descriptionCourte,
                'description' => $draft->descriptionLongue,
                // Champs obligatoires non fournis par l'IA : à compléter pendant la review.
                'prix' => 0,
                'poids' => 0,
                'stock' => 0,
                'favoris' => false,
                'cover' => $cover,
                'status' => ProductStatus::Draft,
                // Lie le brouillon à son import pour la page de revue groupée.
                'batch_id' => $this->batch()?->id,
            ]);
        } catch (Throwable $e) {
            Storage::disk('public')->delete($cover);

            throw $e;
        }
    }

    public function failed(Throwable $exception): void
    {
        Log::error('AI product import failed', [
            'file' => basename($this->imagePath),
            'import_id' => $this->importId,
            'error' => $exception->getMessage(),
        ]);
    }

    /**
     * Nom choisi par l'IA → id, sans tenir compte de la casse ; inconnu => null (jamais de catégorie créée).
     *
     * @param  array<int, string>  $categories
     */
    private function resolveCategoryId(?string $name, array $categories): ?int
    {
        if ($name === null) {
            return null;
        }

        $wanted = mb_strtolower(mb_trim($name));

        foreach ($categories as $id => $categoryName) {
            if (mb_strtolower(mb_trim($categoryName)) === $wanted) {
                return $id;
            }
        }

        return null;
    }
}
