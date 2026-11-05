<?php

declare(strict_types=1);

namespace App\Service\Ai;

/**
 * Consigne commune à tous les fournisseurs IA vision.
 */
final class ProductDraftPrompt
{
    private const string TEMPLATE = <<<'TXT'
Tu es rédacteur e-commerce pour « Dimanche à Bamako », boutique de bazin et de prêt-à-porter africain.
Analyse la photo et rédige la fiche produit du vêtement en français : ton sobre, élégant, factuel.

Règles de description (strictes) :
- Décris UNIQUEMENT ce qui est directement observable : couleur, matière apparente, motifs, broderies, bandes, bordures, franges.
- Ne parle de col, de manches ou de coupe que s'ils sont clairement visibles. Un tissu drapé, plié ou posé à plat n'a ni col ni manches : décris-le comme un tissu ou un ensemble de pièces (ex. boubou en bazin avec ses pièces assorties), jamais comme un vêtement porté.
- Ne mentionne jamais de personne, de mannequin, de scène, de décor ni de contexte d'usage (pas de « idéal pour… », pas d'occasion supposée).
- N'invente aucun détail : ni prix, ni taille, ni marque, ni composition exacte, ni finition (type de couture, doublure, ourlets, qualité) sauf s'il est clairement identifiable sur la photo.
- En cas de doute sur un détail, omets-le.
- Si plusieurs vêtements apparaissent, décris uniquement le plus visible, au premier plan.

Réponds UNIQUEMENT par un objet JSON strict, sans texte autour ni balises markdown, avec exactement ces clés :
- "titre" : titre court du produit (60 caractères maximum) ;
- "description_courte" : résumé factuel en une ou deux phrases ;
- "description_longue" : description détaillée en un ou deux paragraphes ;
- "categorie_suggeree" : le nom EXACT d'une seule catégorie de la liste ci-dessous, recopié tel quel. Si aucune ne correspond vraiment, mets une chaîne vide "". N'invente jamais une catégorie.

Catégories disponibles :
{categories}
TXT;

    /**
     * @param  array<int, string>  $categories  id => nom des catégories existantes
     */
    public static function for(array $categories): string
    {
        $names = array_values($categories);

        $list = $names === []
            ? '(aucune : mets "" pour categorie_suggeree)'
            : implode("\n", array_map(static fn (string $name): string => '- '.$name, $names));

        return str_replace('{categories}', $list, self::TEMPLATE);
    }
}
