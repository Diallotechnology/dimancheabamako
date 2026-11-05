<?php

declare(strict_types=1);

use App\Service\Ai\AiGenerationException;
use App\Service\Ai\MistralProductDescriptionGenerator;
use App\Service\Ai\ProductDescriptionGenerator;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

// Générateur Mistral (Pixtral) : requête multimodale, parsing défensif, logs sans donnée perso. Aucun appel réseau réel.

function mistralTestImage(): string
{
    $path = sys_get_temp_dir().'/dab-mistral-'.getmypid().'.png';
    $img = imagecreatetruecolor(20, 20);
    imagefill($img, 0, 0, imagecolorallocate($img, 90, 60, 160));
    imagepng($img, $path);

    return $path;
}

function fakeMistral(string $content, int $status = 200): void
{
    Http::fake([
        'api.mistral.ai/*' => Http::response(['choices' => [['message' => ['content' => $content]]]], $status),
    ]);
}

beforeEach(function () {
    config([
        'services.mistral.key' => 'mistral-test-key',
        'services.ai.product_provider' => 'mistral',
    ]);
});

it('is the active provider when configured', function () {
    expect(app(ProductDescriptionGenerator::class))->toBeInstanceOf(MistralProductDescriptionGenerator::class);
});

it('sends a multimodal request with bearer auth and maps the JSON answer', function () {
    fakeMistral(json_encode([
        'titre' => 'Boubou bazin violet',
        'description_courte' => 'Un boubou violet brodé.',
        'description_longue' => 'Bazin riche, broderies argentées.',
        'categorie_suggeree' => 'Boubou',
    ]));

    $draft = (new MistralProductDescriptionGenerator)->generate(mistralTestImage(), []);

    expect($draft->titre)->toBe('Boubou bazin violet')
        ->and($draft->descriptionCourte)->toBe('Un boubou violet brodé.')
        ->and($draft->descriptionLongue)->toBe('Bazin riche, broderies argentées.')
        ->and($draft->categorieSuggeree)->toBe('Boubou');

    Http::assertSent(function (Request $r) {
        $parts = $r['messages'][1]['content'];

        return $r->url() === 'https://api.mistral.ai/v1/chat/completions'
            && $r->hasHeader('Authorization', 'Bearer mistral-test-key')
            && $r['response_format']['type'] === 'json_object'
            && $parts[1]['type'] === 'image_url'
            && str_starts_with($parts[1]['image_url'], 'data:image/png;base64,');
    });
});

it('accepts JSON wrapped in markdown fences', function () {
    fakeMistral("```json\n".json_encode([
        'titre' => 'Robe', 'description_courte' => 'c', 'description_longue' => 'l', 'categorie_suggeree' => 'Robe',
    ])."\n```");

    expect((new MistralProductDescriptionGenerator)->generate(mistralTestImage(), [])->titre)->toBe('Robe');
});

it('throws AiGenerationException and logs status + body only on failure', function (Closure $fake, string $logMessage) {
    Log::spy();
    $fake();

    expect(fn () => (new MistralProductDescriptionGenerator)->generate(mistralTestImage(), []))
        ->toThrow(AiGenerationException::class);

    Log::shouldHaveReceived('error')
        ->withArgs(fn ($message, $context = []) => $message === $logMessage
            && isset($context['status'], $context['body'])
            && ! str_contains(json_encode($context), 'mistral-test-key'))
        ->once();
})->with([
    'HTTP error' => [fn () => Http::fake(['*' => Http::response(['message' => 'Unauthorized'], 401)]), 'Mistral request failed'],
    'invalid JSON' => [fn () => fakeMistral('pas du json'), 'Mistral response is not valid JSON'],
    'missing field' => [fn () => fakeMistral(json_encode(['titre' => 'x', 'description_courte' => 'c'])), 'Mistral response misses field description_longue'],
    'empty content' => [fn () => fakeMistral(''), 'Mistral response has no text'],
]);

it('throws without calling the API when the key is missing', function () {
    config(['services.mistral.key' => null]);
    Http::fake();

    expect(fn () => (new MistralProductDescriptionGenerator)->generate(mistralTestImage(), []))
        ->toThrow(AiGenerationException::class);

    Http::assertNothingSent();
});

it('retries on 429 then succeeds', function () {
    $ok = ['choices' => [['message' => ['content' => json_encode([
        'titre' => 'T', 'description_courte' => 'c', 'description_longue' => 'l', 'categorie_suggeree' => '',
    ])]]]];

    Http::fake(['*' => Http::sequence()->push([], 429)->push($ok)]);

    $draft = (new MistralProductDescriptionGenerator)->generate(mistralTestImage(), []);

    expect($draft->categorieSuggeree)->toBeNull();
    Http::assertSentCount(2);
});

it('does not retry on a 4xx client error', function () {
    Http::fake(['*' => Http::response([], 400)]);

    expect(fn () => (new MistralProductDescriptionGenerator)->generate(mistralTestImage(), []))
        ->toThrow(AiGenerationException::class);

    Http::assertSentCount(1);
});

it('puts the available categories in the system prompt', function () {
    fakeMistral(json_encode(['titre' => 'T', 'description_courte' => 'c', 'description_longue' => 'l', 'categorie_suggeree' => 'Bazin']));

    (new MistralProductDescriptionGenerator)->generate(mistralTestImage(), [1 => 'Bazin', 2 => 'Broderie']);

    Http::assertSent(fn (Request $r) => str_contains($r['messages'][0]['content'], "- Bazin\n- Broderie")
        && str_contains($r['messages'][0]['content'], "N'invente jamais une catégorie"));
});
