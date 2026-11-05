<?php

declare(strict_types=1);

use App\Service\Ai\AiGenerationException;
use App\Service\Ai\OllamaProductDescriptionGenerator;
use App\Service\Ai\ProductDescriptionGenerator;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

// Générateur Ollama (local) : requête /api/chat, extraction défensive du JSON, erreurs propres. Aucun serveur Ollama réel.

function ollamaTestImage(): string
{
    $path = sys_get_temp_dir().'/dab-ollama-'.getmypid().'.png';
    $img = imagecreatetruecolor(20, 20);
    imagefill($img, 0, 0, imagecolorallocate($img, 30, 160, 90));
    imagepng($img, $path);

    return $path;
}

/** @param array<string, mixed> $payload */
function ollamaAnswer(string $content): array
{
    return ['model' => 'llama3.2-vision', 'message' => ['role' => 'assistant', 'content' => $content], 'done' => true];
}

function validOllamaJson(): string
{
    return (string) json_encode([
        'titre' => 'Robe wax verte',
        'description_courte' => 'Une robe verte.',
        'description_longue' => 'Wax vert, coupe évasée.',
        'categorie_suggeree' => 'Robe',
    ]);
}

beforeEach(function () {
    config([
        'services.ai.product_provider' => 'ollama',
        'services.ollama.base_url' => 'http://localhost:11434',
        'services.ollama.model' => 'llama3.2-vision',
        'services.ollama.timeout' => 5,
        'services.ollama.num_ctx' => 8192,
    ]);
});

it('is the active provider when configured', function () {
    expect(app(ProductDescriptionGenerator::class))->toBeInstanceOf(OllamaProductDescriptionGenerator::class);
});

it('sends a non-streaming chat request with the base64 image and no auth', function () {
    Http::fake(['localhost:11434/*' => Http::response(ollamaAnswer(validOllamaJson()))]);

    $draft = (new OllamaProductDescriptionGenerator)->generate(ollamaTestImage(), []);

    expect($draft->titre)->toBe('Robe wax verte')
        ->and($draft->descriptionCourte)->toBe('Une robe verte.')
        ->and($draft->descriptionLongue)->toBe('Wax vert, coupe évasée.')
        ->and($draft->categorieSuggeree)->toBe('Robe');

    Http::assertSent(fn (Request $r) => $r->url() === 'http://localhost:11434/api/chat'
        && $r['model'] === 'llama3.2-vision'
        && $r['stream'] === false
        && $r['format'] === 'json'
        && $r['options']['num_ctx'] === 8192
        && $r['messages'][1]['images'][0] === base64_encode((string) file_get_contents(ollamaTestImage()))
        && ! $r->hasHeader('Authorization'));
});

it('extracts the first valid JSON object from a chatty answer', function (string $content) {
    Http::fake(['*' => Http::response(ollamaAnswer($content))]);

    expect((new OllamaProductDescriptionGenerator)->generate(ollamaTestImage(), [])->titre)->toBe('Robe wax verte');
})->with([
    'markdown fences' => fn () => "```json\n".validOllamaJson()."\n```",
    'text around' => fn () => 'Voici la fiche demandée : '.validOllamaJson().' N\'hésitez pas si besoin.',
    'noise object first' => fn () => '{pas du json} puis '.validOllamaJson(),
    'braces inside strings' => fn () => (string) json_encode([
        'titre' => 'Robe wax verte', 'description_courte' => 'Motif {fleur}', 'description_longue' => 'Détail } et {', 'categorie_suggeree' => 'Robe',
    ]),
]);

it('throws AiGenerationException and logs status + body on failure', function (Closure $fake, string $logMessage) {
    Log::spy();
    $fake();

    expect(fn () => (new OllamaProductDescriptionGenerator)->generate(ollamaTestImage(), []))
        ->toThrow(AiGenerationException::class);

    Log::shouldHaveReceived('error')
        ->withArgs(fn ($message, $context = []) => $message === $logMessage && isset($context['status'], $context['body']))
        ->once();
})->with([
    'HTTP error (model missing)' => [fn () => Http::fake(['*' => Http::response(['error' => 'model "llama3.2-vision" not found'], 404)]), 'Ollama request failed'],
    'invalid JSON' => [fn () => Http::fake(['*' => Http::response(ollamaAnswer('désolé, je ne peux pas'))]), 'Ollama response is not valid JSON'],
    'missing field' => [fn () => Http::fake(['*' => Http::response(ollamaAnswer((string) json_encode(['titre' => 'x', 'description_courte' => 'c'])))]), 'Ollama response misses field description_longue'],
    'empty content' => [fn () => Http::fake(['*' => Http::response(ollamaAnswer(''))]), 'Ollama response has no text'],
]);

it('fails with AiGenerationException when the server is unreachable or times out', function () {
    Log::spy();
    Http::fake(['*' => Http::failedConnection()]);

    expect(fn () => (new OllamaProductDescriptionGenerator)->generate(ollamaTestImage(), []))
        ->toThrow(AiGenerationException::class);

    Log::shouldHaveReceived('error')
        ->withArgs(fn ($message, $context = []) => $message === 'Ollama request failed' && isset($context['error']))
        ->once();
});

it('retries once on a refused connection then succeeds', function () {
    Http::fake(['*' => Http::sequence()->pushFailedConnection()->push(ollamaAnswer(validOllamaJson()))]);

    $draft = (new OllamaProductDescriptionGenerator)->generate(ollamaTestImage(), []);

    expect($draft->titre)->toBe('Robe wax verte');
    Http::assertSentCount(2);
});

it('does not retry an HTTP error answer', function () {
    Http::fake(['*' => Http::response(['error' => 'boom'], 500)]);

    expect(fn () => (new OllamaProductDescriptionGenerator)->generate(ollamaTestImage(), []))
        ->toThrow(AiGenerationException::class);

    Http::assertSentCount(1);
});

it('does not retry after a timeout', function () {
    $attempts = 0;
    Http::fake(function () use (&$attempts) {
        $attempts++;

        throw new ConnectionException('cURL error 28: Operation timed out after 5001 milliseconds');
    });

    expect(fn () => (new OllamaProductDescriptionGenerator)->generate(ollamaTestImage(), []))
        ->toThrow(AiGenerationException::class);

    expect($attempts)->toBe(1);
});

it('puts the available categories in the system prompt', function () {
    Http::fake(['*' => Http::response(ollamaAnswer(validOllamaJson()))]);

    (new OllamaProductDescriptionGenerator)->generate(ollamaTestImage(), [1 => 'Bazin', 2 => 'Broderie']);

    Http::assertSent(fn (Request $r) => str_contains($r['messages'][0]['content'], "- Bazin\n- Broderie")
        && str_contains($r['messages'][0]['content'], "N'invente jamais une catégorie"));
});
