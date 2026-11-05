<?php

declare(strict_types=1);

namespace App\Service\Ai;

interface ProductDescriptionGenerator
{
    /**
     * Génère une fiche produit à partir d'une photo (chemin local du fichier).
     *
     * @param  array<int, string>  $categories  id => nom des catégories existantes (le modèle doit choisir parmi elles)
     *
     * @throws AiGenerationException
     */
    public function generate(string $imagePath, array $categories): ProductDraftData;
}
