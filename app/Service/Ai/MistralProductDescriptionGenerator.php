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

final class MistralProductDescriptionGenerator implements ProductDescriptionGenerator
{
    /**
     * @throws AiGenerationException
     */
    public function generate(string $imagePath, array $categories): ProductDraftData
    {
        $key = config('services.mistral.key');

        if (! is_string($key) || $key === '') {
            throw new AiGenerationException('Clé API Mistral manquante.');
        }

        $contents = @file_get_contents($imagePath);
        if ($contents === false) {
            throw new AiGenerationException('Image illisible : '.basename($imagePath));
        }

        $response = $this->call($key, $contents, File::mimeType($imagePath) ?: 'image/jpeg', $categories);

        if (! $response->successful()) {
            $this->logFailure('Mistral request failed', $response);

            throw new AiGenerationException('Mistral a refusé la requête (HTTP '.$response->status().').');
        }

        return $this->parse($response);
    }

    /**
     * @param  array<int, string>  $categories
     */
    private function call(string $key, string $contents, string $mime, array $categories): Response
    {
        $url = mb_rtrim((string) config('services.mistral.base_url'), '/').'/chat/completions';

        try {
            return Http::withToken($key)
                ->timeout(60)
                ->retry(
                    2,
                    500,
                    fn ($e): bool => $e instanceof ConnectionException
                        || ($e instanceof RequestException && in_array($e->response->status(), [429, 500, 502, 503, 504], true)),
                    throw: false,
                )
                ->post($url, [
                    'model' => config('services.mistral.model'),
                    'temperature' => 0.4,
                    'response_format' => ['type' => 'json_object'],
                    'messages' => [
                        ['role' => 'system', 'content' => ProductDraftPrompt::for($categories)],
                        [
                            'role' => 'user',
                            'content' => [
                                ['type' => 'text', 'text' => 'Rédige la fiche produit de ce vêtement.'],
                                ['type' => 'image_url', 'image_url' => "data:{$mime};base64,".base64_encode($contents)],
                            ],
                        ],
                    ],
                ]);
        } catch (ConnectionException $e) {
            Log::error('Mistral request failed', ['error' => Str::limit($e->getMessage(), 200)]);

            throw new AiGenerationException('Mistral injoignable.', previous: $e);
        }
    }

    private function parse(Response $response): ProductDraftData
    {
        $text = data_get($response->json(), 'choices.0.message.content');

        if (! is_string($text) || mb_trim($text) === '') {
            $this->logFailure('Mistral response has no text', $response);

            throw new AiGenerationException('Réponse Mistral vide.');
        }

        // Défensif : certains modèles entourent le JSON de balises ```json malgré la consigne.
        $text = (string) preg_replace('/^```(?:json)?\s*|\s*```$/i', '', mb_trim($text));

        try {
            $data = json_decode($text, true, 8, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            $this->logFailure('Mistral response is not valid JSON', $response);

            throw new AiGenerationException('Réponse Mistral : JSON invalide.');
        }

        if (! is_array($data)) {
            $this->logFailure('Mistral response is not a JSON object', $response);

            throw new AiGenerationException('Réponse Mistral : objet JSON attendu.');
        }

        foreach (['titre', 'description_courte', 'description_longue'] as $field) {
            if (! isset($data[$field]) || ! is_string($data[$field]) || mb_trim($data[$field]) === '') {
                $this->logFailure("Mistral response misses field {$field}", $response);

                throw new AiGenerationException("Réponse Mistral : champ « {$field} » manquant ou vide.");
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
