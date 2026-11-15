<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\ProductImportRequest;
use App\Jobs\ProcessProductImageJob;
use App\Models\Category;
use App\Models\Product;
use App\Support\Options;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use RuntimeException;

final class ProductImportController extends Controller
{
    public const string CONNECTION = 'database';

    public function create(): Response
    {
        return Inertia::render('Admin/Product/Import');
    }

    public function store(ProductImportRequest $request): JsonResponse
    {
        $importId = Str::uuid()->toString();
        $disk = Storage::disk('local');
        $directory = 'imports/'.$importId;

        // Chargées une seule fois pour tout le batch (sinon une requête par job).
        $categories = Category::query()->orderBy('nom')->pluck('nom', 'id')->all();

        $jobs = [];
        foreach ($request->file('images', []) as $file) {
            // Copie en flux depuis le chemin reçu par PHP : `storeAs()` passe par `getRealPath()`, qui
            // échoue quand le dossier temporaire d'upload est illisible pour le serveur web (Windows).
            $stored = $directory.'/'.Str::uuid()->toString().'.'.$file->extension();
            $stream = fopen($file->getPathname(), 'rb');

            if ($stream === false) {
                throw new RuntimeException('Impossible de lire la photo envoyée.');
            }

            try {
                $disk->put($stored, $stream);
            } finally {
                fclose($stream);
            }

            $jobs[] = new ProcessProductImageJob($disk->path($stored), $importId, $categories);
        }

        $batch = Bus::batch($jobs)
            ->name('import-'.$importId)
            ->onConnection(self::CONNECTION)
            ->allowFailures()
            ->finally(static function () use ($directory): void {
                Storage::disk('local')->deleteDirectory($directory);
            })
            ->dispatch();

        return response()->json(['batch_id' => $batch->id, 'total' => $batch->totalJobs], 202);
    }

    public function review(string $batchId): Response
    {
        abort_if(Bus::findBatch($batchId) === null, 404);

        $products = Product::query()
            ->where('batch_id', $batchId)
            ->orderBy('id')
            ->get([
                'id', 'categorie_id', 'reference', 'nom', 'resume', 'description',
                'color', 'taille', 'poids', 'prix', 'stock', 'favoris', 'is_preorder', 'cover', 'status',
            ]);

        $category = Options::from(Category::query()->orderBy('nom')->get(['id', 'nom']), fn (Category $row): string => $row->nom);

        return Inertia::render('Admin/Product/ImportReview', [
            'batchId' => $batchId,
            'products' => $products,
            'category' => $category,
        ]);
    }

    public function show(string $batchId): JsonResponse
    {
        $batch = Bus::findBatch($batchId);

        abort_if($batch === null, 404);

        // `allowFailures()` : les jobs en échec restent dans `pendingJobs` et `finished_at` n'est jamais
        // renseigné. `finished()` / `progress()` de Laravel sont donc faux dès le premier échec ; on les
        // recalcule ici : il reste à traiter ce qui est en attente sans être en échec.
        $remaining = $batch->pendingJobs - $batch->failedJobs;

        return response()->json([
            'total' => $batch->totalJobs,
            'processed' => $batch->processedJobs(),
            'failed' => $batch->failedJobs,
            'finished' => $remaining <= 0,
            'progress' => $batch->totalJobs > 0
                ? (int) round(($batch->totalJobs - $remaining) / $batch->totalJobs * 100)
                : 100,
        ]);
    }
}
