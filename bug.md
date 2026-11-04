# Bugs hors périmètre

Bugs rencontrés pendant d'autres chantiers, notés ici pour être corrigés ensuite (un fix = un test si sécurité/finance, puis retirer l'entrée).

## 3. `CACHE_STORE` du `.env` ignoré (config lit `CACHE_DRIVER`)

- **Fichier :** `config/cache.php:20` (`'default' => env('CACHE_DRIVER', 'file')`) ; `.env` local contient `CACHE_STORE=database`.
- **Découvert :** 2026-11-04, étape 4 de l'upgrade Laravel 13 (analyse du cache).
- **Problème :** depuis Laravel 11 le squelette lit `CACHE_STORE`. Ici la variable est ignorée : le cache (panier, catégories, taux EUR) tourne sur le driver `file`, quelle que soit la valeur de `CACHE_STORE`.
- **Piste :** aligner `config/cache.php` sur `env('CACHE_STORE', 'file')` après avoir vérifié la valeur réellement voulue en production (`database` suppose la table `cache`). Vérifier aussi `.env` de prod avant de changer.
