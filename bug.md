# Bugs hors périmètre

Bugs rencontrés pendant d'autres chantiers, notés ici pour être corrigés ensuite (un fix = un test si sécurité/finance, puis retirer l'entrée).

## 1. `VerifyEmailController::__invoke` sans `return` dans un cas

- **Fichier :** `app/Http/Controllers/Auth/VerifyEmailController.php:27`
- **Découvert :** 2026-11-04, en ajoutant Larastan (branche `chore/larastan`), règle `return.missing` (non ignorable par PHPStan).
- **Problème :** la méthode est typée `RedirectResponse`, mais si `markEmailAsVerified()` renvoie `false`, aucune branche ne retourne : `TypeError` (500) au lieu d'une redirection.
- **Contournement actuel :** fichier exclu via `excludePaths` dans `phpstan.neon`. À retirer une fois corrigé.
- **Piste :** retourner une redirection (avec message d'erreur) dans le cas `false`, et dédupliquer les deux branches identiques.

## 2. `pint --test` échoue sur 13 modèles (docblocks)

- **Fichiers :** `app/Models/*.php` (Category, Client, Country, Devise, Image, PayLink, PendingRegistration, Poids, Promotion, Slide, Transport, User, Zone).
- **Découvert :** 2026-11-04, étape 1 de l'upgrade Laravel 13. Présent avant l'étape 1 (modèles non touchés).
- **Problème :** règles `phpdoc_separation` / `fully_qualified_strict_types` sur les docblocks générés par ide-helper (`@property`, `@method`).
- **Piste :** `vendor/bin/pint app/Models` (ou exclure les docblocks ide-helper dans `pint.json`), puis relancer `php artisan ide-helper:models -M` pour vérifier que le format est stable.
