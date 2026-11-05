<?php

declare(strict_types=1);

namespace App\Service\Ai;

final readonly class ProductDraftData
{
    public function __construct(
        public string $titre,
        public string $descriptionCourte,
        public string $descriptionLongue,
        public ?string $categorieSuggeree,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            titre: (string) $data['titre'],
            descriptionCourte: (string) $data['description_courte'],
            descriptionLongue: (string) $data['description_longue'],
            categorieSuggeree: isset($data['categorie_suggeree']) && $data['categorie_suggeree'] !== ''
                ? (string) $data['categorie_suggeree']
                : null,
        );
    }

    /**
     * Forme sérialisable (cache) : un simple tableau, jamais l'objet.
     *
     * @return array{titre: string, description_courte: string, description_longue: string, categorie_suggeree: string|null}
     */
    public function toArray(): array
    {
        return [
            'titre' => $this->titre,
            'description_courte' => $this->descriptionCourte,
            'description_longue' => $this->descriptionLongue,
            'categorie_suggeree' => $this->categorieSuggeree,
        ];
    }
}
