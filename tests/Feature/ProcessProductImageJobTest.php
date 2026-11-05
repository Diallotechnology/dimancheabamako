<?php

declare(strict_types=1);

use App\Enum\ProductStatus;
use App\Jobs\ProcessProductImageJob;
use App\Models\Category;
use App\Models\Product;
use App\Service\Ai\GeminiProductDescriptionGenerator;
use App\Service\Ai\ProductDescriptionGenerator;
use App\Service\Ai\ProductDraftPrompt;
use Illuminate\Bus\PendingBatch;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

// Pipeline d'import IA : brouillon créé depuis une photo, cache par hash, échecs propres. Aucun appel réseau réel.

function aiTestImage(string $name = 'boubou-bleu.png', int $shade = 80): string
{
    $dir = sys_get_temp_dir().'/dab-ai-'.getmypid();
    @mkdir($dir);
    $path = $dir.'/'.$name;

    $img = imagecreatetruecolor(40, 40);
    imagefill($img, 0, 0, imagecolorallocate($img, $shade, 120, 200));
    imagepng($img, $path);

    return $path;
}

function fakeGemini(array $payload = [], int $status = 200): void
{
    $payload += [
        'titre' => 'Boubou bazin bleu brodé',
        'description_courte' => 'Un boubou bleu brodé à la main.',
        'description_longue' => 'Bazin riche bleu nuit, broderies dorées au col.',
        'categorie_suggeree' => 'boubou',
    ];

    Http::fake([
        'generativelanguage.googleapis.com/*' => Http::response(
            ['candidates' => [['content' => ['parts' => [['text' => json_encode($payload)]]]]]],
            $status,
        ),
    ]);
}

beforeEach(function () {
    // Ces tests simulent l'API Gemini : on fixe le fournisseur (le défaut de l'app est Mistral).
    config(['services.gemini.key' => 'test-key', 'services.ai.product_provider' => 'gemini']);
    Storage::fake('public');
    Cache::flush();
});

it('binds the Gemini implementation to the generator interface', function () {
    expect(app(ProductDescriptionGenerator::class))->toBeInstanceOf(GeminiProductDescriptionGenerator::class);
});

it('creates a draft product from the AI output with a unique reference', function () {
    Category::create(['nom' => 'Boubou', 'description' => 'x', 'promo' => 0]);
    fakeGemini();

    dispatch_sync(new ProcessProductImageJob(aiTestImage('a.png', 10), 'import-1'));
    dispatch_sync(new ProcessProductImageJob(aiTestImage('b.png', 200), 'import-1'));

    $products = Product::all();
    expect($products)->toHaveCount(2);

    $product = $products->first();
    expect($product->status)->toBe(ProductStatus::Draft)
        ->and($product->nom)->toBe('Boubou bazin bleu brodé')
        ->and($product->resume)->toBe('Un boubou bleu brodé à la main.')
        ->and($product->description)->toBe('Bazin riche bleu nuit, broderies dorées au col.')
        ->and($product->categorie_id)->toBe(Category::where('nom', 'Boubou')->value('id'))
        ->and($products->pluck('reference')->unique())->toHaveCount(2)
        ->and($product->reference)->toStartWith('AI-');

    Storage::disk('public')->assertExists($product->getRawOriginal('cover'));

    Http::assertSent(fn (Request $r) => $r->hasHeader('x-goog-api-key', 'test-key')
        && ! str_contains($r->url(), 'test-key'));
});

it('hides drafts from the storefront scope', function () {
    fakeGemini();
    dispatch_sync(new ProcessProductImageJob(aiTestImage(), 'import-1'));

    expect(Product::published()->count())->toBe(0)
        ->and(Product::count())->toBe(1);
});

it('does not call Gemini twice for the same image content', function () {
    fakeGemini();

    // Deux fichiers différents, même contenu => même hash.
    $first = aiTestImage('un.png', 50);
    $second = dirname($first).'/deux.png';
    copy($first, $second);

    dispatch_sync(new ProcessProductImageJob($first, 'import-1'));
    dispatch_sync(new ProcessProductImageJob($second, 'import-1'));

    Http::assertSentCount(1);
    expect(Product::count())->toBe(2);
});

it('is compatible with Bus::batch', function () {
    Bus::fake();

    Bus::batch([new ProcessProductImageJob(aiTestImage(), 'import-1')])->dispatch();

    Bus::assertBatched(fn (PendingBatch $batch) => $batch->jobs->count() === 1
        && $batch->jobs->first() instanceof ProcessProductImageJob);
});

it('fails cleanly without creating a product when Gemini errors or answers badly', function (callable $fake, string $logMessage) {
    Log::spy();
    $fake();

    expect(fn () => dispatch_sync(new ProcessProductImageJob(aiTestImage('robe-client.png'), 'import-9')))
        ->toThrow(Exception::class);

    expect(Product::count())->toBe(0)
        ->and(Storage::disk('public')->allFiles())->toBe([]);

    // Log du générateur : statut + body, sans donnée personnelle.
    Log::shouldHaveReceived('error')
        ->withArgs(fn ($message, $context = []) => $message === $logMessage && isset($context['status'], $context['body']))
        ->once();

    // Log du job : nom de fichier uniquement (pas le chemin complet).
    Log::shouldHaveReceived('error')
        ->withArgs(fn ($message, $context = []) => $message === 'AI product import failed'
            && $context['file'] === 'robe-client.png'
            && ! str_contains(json_encode($context), sys_get_temp_dir()))
        ->once();
})->with([
    'HTTP error' => [fn () => Http::fake(['*' => Http::response(['error' => ['message' => 'API key not valid']], 400)]), 'Gemini request failed'],
    'invalid JSON' => [fn () => Http::fake(['*' => Http::response(['candidates' => [['content' => ['parts' => [['text' => 'pas du json']]]]]])]), 'Gemini response is not valid JSON'],
    'missing field' => [fn () => fakeGemini(['description_longue' => '']), 'Gemini response misses field description_longue'],
]);

it('does not cache a failed generation', function () {
    $ok = ['candidates' => [['content' => ['parts' => [['text' => json_encode([
        'titre' => 'Robe', 'description_courte' => 'c', 'description_longue' => 'l', 'categorie_suggeree' => 'Robe',
    ])]]]]]];
    Http::fake(['*' => Http::sequence()->push([], 400)->push($ok)]);
    $image = aiTestImage();

    expect(fn () => dispatch_sync(new ProcessProductImageJob($image, 'import-1')))->toThrow(Exception::class);

    dispatch_sync(new ProcessProductImageJob($image, 'import-1'));

    Http::assertSentCount(2);
    expect(Product::count())->toBe(1);
});

it('builds a prompt that lists the real categories and forbids inventing details', function () {
    $prompt = ProductDraftPrompt::for([1 => 'Bazin', 2 => 'Broderie']);

    expect($prompt)->toContain("- Bazin\n- Broderie")
        ->toContain("N'invente jamais une catégorie")
        ->toContain('Ne mentionne jamais de personne')
        ->toContain('UNIQUEMENT ce qui est directement observable');

    expect(ProductDraftPrompt::for([]))->toContain('aucune');
});

it('sends the existing category names to the AI and resolves the answer case-insensitively', function () {
    $bazin = Category::create(['nom' => 'Bazin', 'description' => 'x', 'promo' => 0]);
    Category::create(['nom' => 'Broderie', 'description' => 'x', 'promo' => 0]);
    fakeGemini(['categorie_suggeree' => ' BAZIN ']);

    dispatch_sync(new ProcessProductImageJob(aiTestImage(), 'import-1'));

    Http::assertSent(fn (Request $r) => str_contains($r['contents'][0]['parts'][0]['text'], "- Bazin\n- Broderie"));
    expect(Product::first()->categorie_id)->toBe($bazin->id);
});

it('leaves the category empty when the AI answers with an unknown one, without creating it', function () {
    Category::create(['nom' => 'Bazin', 'description' => 'x', 'promo' => 0]);
    fakeGemini(['categorie_suggeree' => 'Costume trois pièces']);

    dispatch_sync(new ProcessProductImageJob(aiTestImage(), 'import-1'));

    expect(Product::first()->categorie_id)->toBeNull()
        ->and(Category::count())->toBe(1);
});

it('uses the categories passed to the job and keys the cache on them', function () {
    $robe = Category::create(['nom' => 'Robe', 'description' => 'x', 'promo' => 0]);
    fakeGemini(['categorie_suggeree' => 'Robe']);
    $image = aiTestImage();

    dispatch_sync(new ProcessProductImageJob($image, 'import-1', [$robe->id => 'Robe']));
    dispatch_sync(new ProcessProductImageJob($image, 'import-1', [$robe->id => 'Robe']));
    Http::assertSentCount(1);

    // Liste de catégories différente => la réponse en cache n'est plus valable.
    dispatch_sync(new ProcessProductImageJob($image, 'import-1', [$robe->id => 'Robe', 99 => 'Boubou']));
    Http::assertSentCount(2);

    expect(Product::pluck('categorie_id')->unique()->all())->toBe([$robe->id]);
});

it('links the created draft to its job batch', function () {
    fakeGemini();

    $batch = Bus::batch([new ProcessProductImageJob(aiTestImage(), 'import-1')])->onConnection('sync')->dispatch();

    expect(Product::sole()->batch_id)->toBe($batch->id);
});
