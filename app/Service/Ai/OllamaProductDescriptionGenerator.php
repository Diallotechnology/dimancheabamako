<?php

declare(strict_types=1);

namespace App\Service\Ai;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use JsonException;

/**
 * Fournisseur local (Ollama, API /api/chat) : pas de clé, pas de quota, mais des modèles lents et moins disciplinés sur le JSON.
 */
final class OllamaProductDescriptionGenerator implements ProductDescriptionGenerator
{
    /**
     * @throws AiGenerationException
     */
    public function generate(string $imagePath, array $categories): ProductDraftData
    {
        $contents = @file_get_contents($imagePath);
        if ($contents === false) {
            throw new AiGenerationException('Image illisible : '.basename($imagePath));
        }

        $response = $this->call($contents, $categories);

        if (! $response->successful()) {
            $this->logFailure('Ollama request failed', $response);

            throw new AiGenerationException('Ollama a refusé la requête (HTTP '.$response->status().').');
        }

        return $this->parse($response);
    }

    /**
     * @param  array<int, string>  $categories
     */
    private function call(string $contents, array $categories): Response
    {
        $url = mb_rtrim((string) config('services.ollama.base_url'), '/').'/api/chat';

        try {
            return Http::timeout((int) config('services.ollama.timeout'))
                // Retry léger sur connexion refusée (serveur pas encore démarré) uniquement :
                // retenter après un timeout (cURL 28) doublerait l'attente d'un modèle déjà lent.
                ->retry(2, 1000, fn ($e): bool => $e instanceof ConnectionException && ! str_contains($e->getMessage(), 'cURL error 28'), throw: false)
                ->post($url, [
                    'model' => config('services.ollama.model'),
                    'stream' => false,
                    'format' => 'json',
                    // num_ctx : l'image consomme ~4000 tokens ; avec le contexte par défaut (4096) la réponse est coupée net
                    // (done_reason « length ») et le JSON invalide. Température basse : choix de catégorie reproductible.
                    'options' => ['temperature' => 0.1, 'num_ctx' => (int) config('services.ollama.num_ctx')],
                    'messages' => [
                        ['role' => 'system', 'content' => ProductDraftPrompt::for($categories)],
                        [
                            'role' => 'user',
                            'content' => 'Rédige la fiche produit de ce vêtement.',
                            'images' => [base64_encode($contents)],
                        ],
                    ],
                ]);
        } catch (ConnectionException $e) {
            Log::error('Ollama request failed', ['error' => Str::limit($e->getMessage(), 200)]);

            throw new AiGenerationException('Ollama injoignable ou trop lent.', previous: $e);
        }
    }

    private function parse(Response $response): ProductDraftData
    {
        $text = data_get($response->json(), 'message.content');

        if (! is_string($text) || mb_trim($text) === '') {
            $this->logFailure('Ollama response has no text', $response);

            throw new AiGenerationException('Réponse Ollama vide.');
        }

        $data = $this->extractJson($text);

        if ($data === null) {
            $this->logFailure('Ollama response is not valid JSON', $response);

            throw new AiGenerationException('Réponse Ollama : JSON invalide.');
        }

        foreach (['titre', 'description_courte', 'description_longue'] as $field) {
            if (! isset($data[$field]) || ! is_string($data[$field]) || mb_trim($data[$field]) === '') {
                $this->logFailure("Ollama response misses field {$field}", $response);

                throw new AiGenerationException("Réponse Ollama : champ « {$field} » manquant ou vide.");
            }
        }

        if (isset($data['categorie_suggeree']) && ! is_string($data['categorie_suggeree'])) {
            $data['categorie_suggeree'] = null;
        }

        return ProductDraftData::fromArray($data);
    }

    /**
     * Les modèles locaux ajoutent souvent du texte ou des balises autour du JSON :
     * on tente d'abord la réponse entière, puis le premier objet JSON équilibré qu'elle contient.
     *
     * @return array<string, mixed>|null
     */
    private function extractJson(string $text): ?array
    {
        $text = mb_trim($text);

        foreach ([$text, ...$this->balancedObjects($text)] as $candidate) {
            try {
                $data = json_decode($candidate, true, 8, JSON_THROW_ON_ERROR);
            } catch (JsonException) {
                continue;
            }

            if (is_array($data) && ! array_is_list($data)) {
                return $data;
            }
        }

        return null;
    }

    /**
     * Sous-chaînes `{ ... }` équilibrées (guillemets et échappements respectés), dans l'ordre d'apparition.
     *
     * @return list<string>
     */
    private function balancedObjects(string $text): array
    {
        $objects = [];
        $length = mb_strlen($text);

        for ($start = mb_strpos($text, '{'); $start !== false && $start < $length; $start = mb_strpos($text, '{', $start + 1)) {
            $depth = 0;
            $inString = false;
            $escaped = false;

            for ($i = $start; $i < $length; $i++) {
                $char = $text[$i];

                if ($inString) {
                    if ($escaped) {
                        $escaped = false;
                    } elseif ($char === '\\') {
                        $escaped = true;
                    } elseif ($char === '"') {
                        $inString = false;
                    }

                    continue;
                }

                if ($char === '"') {
                    $inString = true;
                } elseif ($char === '{') {
                    $depth++;
                } elseif ($char === '}' && --$depth === 0) {
                    $objects[] = mb_substr($text, $start, $i - $start + 1);

                    break;
                }
            }
        }

        return $objects;
    }

    /**
     * Statut + body uniquement (tronqué) : l'image envoyée n'est jamais loggée.
     */
    private function logFailure(string $message, Response $response): void
    {
        Log::error($message, [
            'status' => $response->status(),
            'body' => Str::limit($response->body(), 2000),
        ]);
    }
}
