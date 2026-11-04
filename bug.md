# Bugs hors périmètre

Bugs rencontrés pendant d'autres chantiers, notés ici pour être corrigés ensuite (un fix = un test si sécurité/finance, puis retirer l'entrée).

## 1. `VerifyEmailController::__invoke` sans `return` dans un cas

- **Fichier :** `app/Http/Controllers/Auth/VerifyEmailController.php:27`
- **Découvert :** 2026-11-04, en ajoutant Larastan (branche `chore/larastan`), règle `return.missing` (non ignorable par PHPStan).
- **Problème :** la méthode est typée `RedirectResponse`, mais si `markEmailAsVerified()` renvoie `false`, aucune branche ne retourne : `TypeError` (500) au lieu d'une redirection.
- **Contournement actuel :** fichier exclu via `excludePaths` dans `phpstan.neon`. À retirer une fois corrigé.
- **Piste :** retourner une redirection (avec message d'erreur) dans le cas `false`, et dédupliquer les deux branches identiques.
