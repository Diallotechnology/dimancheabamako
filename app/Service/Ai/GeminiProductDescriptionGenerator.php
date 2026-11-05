<?php

declare(strict_types=1);

namespace App\Service\Ai;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use JsonException;

final class GeminiProductDescriptionGenerator implements ProductDescriptionGenerator
{
    /**
     * @throws AiGenerationException
     */
    public function generate(string $imagePath, array $categories): ProductDraftData
    {
        $key = config('services.gemini.key');

        if (! is_string($key) || $key === '') {
            throw new AiGenerationException('Clé API Gemini manquante.');
        }

        $contents = @file_get_contents($imagePath);
        if ($contents === false) {
            throw new AiGenerationException('Image illisible : '.basename($imagePath));
        }

        $response = $this->call($key, $contents, File::mimeType($imagePath) ?: 'image/jpeg', $categories);

        if (! $response->successful()) {
            $this->logFailure('Gemini request failed', $response);

            throw new AiGenerationException('Gemini a refusé la requête (HTTP '.$response->status().').');
        }

        return $this->parse($response);
    }

    /**
     * @param  array<int, string>  $categories
     */
    private function call(string $key, string $contents, string $mime, array $categories): Response
    {
        $url = mb_rtrim((string) config('services.gemini.base_url'), '/')
            .'/models/'.config('services.gemini.model').':generateContent';

        try {
            return Http::withHeaders(['x-goog-api-key' => $key])
                ->timeout(60)
                ->retry(
                    2,
                    500,
                    fn ($e): bool => $e instanceof ConnectionException
                        || ($e instanceof RequestException && in_array($e->response->status(), [429, 500, 502, 503, 504], true)),
                    throw: false,
                )
                ->post($url, [
                    'contents' => [[
                        'parts' => [
                            ['text' => ProductDraftPrompt::for($categories)],
                            ['inline_data' => ['mime_type' => $mime, 'data' => base64_encode($contents)]],
                        ],
                    ]],
                    'generationConfig' => [
                        'temperature' => 0.4,
                        'responseMimeType' => 'application/json',
                        'responseSchema' => [
                            'type' => 'OBJECT',
                            'properties' => [
                                'titre' => ['type' => 'STRING'],
                                'description_courte' => ['type' => 'STRING'],
                                'description_longue' => ['type' => 'STRING'],
                                'categorie_suggeree' => ['type' => 'STRING'],
                            ],
                            'required' => ['titre', 'description_courte', 'description_longue', 'categorie_suggeree'],
                        ],
                    ],
                ]);
        } catch (ConnectionException $e) {
            Log::error('Gemini request failed', ['error' => Str::limit($e->getMessage(), 200)]);

            throw new AiGenerationException('Gemini injoignable.', previous: $e);
        }
    }

    private function parse(Response $response): ProductDraftData
    {
        $text = data_get($response->json(), 'candidates.0.content.parts.0.text');

        if (! is_string($text) || mb_trim($text) === '') {
            $this->logFailure('Gemini response has no text', $response);

            throw new AiGenerationException('Réponse Gemini vide.');
        }

        // Défensif : certains modèles entourent le JSON de balises ```json malgré la consigne.
        $text = (string) preg_replace('/^```(?:json)?\s*|\s*```$/i', '', mb_trim($text));

        try {
            $data = json_decode($text, true, 8, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            $this->logFailure('Gemini response is not valid JSON', $response);

            throw new AiGenerationException('Réponse Gemini : JSON invalide.');
        }

        if (! is_array($data)) {
            $this->logFailure('Gemini response is not a JSON object', $response);

            throw new AiGenerationException('Réponse Gemini : objet JSON attendu.');
        }

        foreach (['titre', 'description_courte', 'description_longue'] as $field) {
            if (! isset($data[$field]) || ! is_string($data[$field]) || mb_trim($data[$field]) === '') {
                $this->logFailure("Gemini response misses field {$field}", $response);

                throw new AiGenerationException("Réponse Gemini : champ « {$field} » manquant ou vide.");
            }
        }

        if (isset($data['categorie_suggeree']) && ! is_string($data['categorie_suggeree'])) {
            $data['categorie_suggeree'] = null;
        }

        return ProductDraftData::fromArray($data);
    }

    /**
     * Statut + body uniquement : l'image envoyée et la clé API ne sont jamais loggées.
     */
    private function logFailure(string $message, Response $response): void
    {
        Log::error($message, [
            'status' => $response->status(),
            'body' => Str::limit($response->body(), 2000),
        ]);
    }
}
