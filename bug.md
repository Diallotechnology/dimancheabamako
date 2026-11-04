# Bugs hors périmètre

Bugs rencontrés pendant d'autres chantiers, notés ici pour être corrigés ensuite (un fix = un test si sécurité/finance, puis retirer l'entrée).

## 2. `pint --test` échoue sur 13 modèles (docblocks)

- **Fichiers :** `app/Models/*.php` (Category, Client, Country, Devise, Image, PayLink, PendingRegistration, Poids, Promotion, Slide, Transport, User, Zone).
- **Découvert :** 2026-11-04, étape 1 de l'upgrade Laravel 13. Présent avant l'étape 1 (modèles non touchés).
- **Problème :** règles `phpdoc_separation` / `fully_qualified_strict_types` sur les docblocks générés par ide-helper (`@property`, `@method`).
- **Piste :** `vendor/bin/pint app/Models` (ou exclure les docblocks ide-helper dans `pint.json`), puis relancer `php artisan ide-helper:models -M` pour vérifier que le format est stable.

## 3. `CACHE_STORE` du `.env` ignoré (config lit `CACHE_DRIVER`)

- **Fichier :** `config/cache.php:20` (`'default' => env('CACHE_DRIVER', 'file')`) ; `.env` local contient `CACHE_STORE=database`.
- **Découvert :** 2026-11-04, étape 4 de l'upgrade Laravel 13 (analyse du cache).
- **Problème :** depuis Laravel 11 le squelette lit `CACHE_STORE`. Ici la variable est ignorée : le cache (panier, catégories, taux EUR) tourne sur le driver `file`, quelle que soit la valeur de `CACHE_STORE`.
- **Piste :** aligner `config/cache.php` sur `env('CACHE_STORE', 'file')` après avoir vérifié la valeur réellement voulue en production (`database` suppose la table `cache`). Vérifier aussi `.env` de prod avant de changer.

## 4. `DatabaseSeeder` : aucune zone n'a de transporteur ni de tarif utilisable

- **Fichier :** `database/seeders/DatabaseSeeder.php` (`Transport::factory()->hasZones(2)`, `ShippingFactory` avec `rand(1, 2)`).
- **Découvert :** 2026-11-04, test navigateur du panier (étape 5 de l'upgrade Laravel 13).
- **Problème :** les pays sont tous dans la zone 1, mais les zones liées aux transporteurs sont d'autres zones créées par `hasZones(2)`. Après `db:seed`, le menu « Transporteur » du panier reste vide, donc la livraison ne peut pas se calculer en local sans retouche manuelle.
- **Piste :** lier explicitement les transporteurs à la zone des pays (`$zone->transports()->attach(...)`) et créer des `Shipping` cohérents avec les `Poids` (zone, transport, poids).
