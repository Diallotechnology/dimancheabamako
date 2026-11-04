# Bugs hors périmètre

Bugs rencontrés pendant d'autres chantiers, notés ici pour être corrigés ensuite (un fix = un test si sécurité/finance, puis retirer l'entrée).

## 2026-11-04 — Paiement N-Genius invisible en cas d'échec de création d'ordre

- **Où** : `app/Http/Controllers/OrderController.php:63-65` (URLs de retour) et `:80-85` (catch), `app/Helper/OrderAPI.php:133-138`.
- **Cause** : le sandbox N-Genius refuse les URLs de redirection non publiques (`http(s)://localhost`, `*.test`) → 422 `Invalid redirect url`. Avec `APP_URL=http://localhost`, la commande échoue en local.
- **Symptôme** : message générique « La validation a échoué », rien d'exploitable dans `laravel.log`. Le `Log::error('Order failed rollback')` ne contient que le message d'exception, pas le corps de la réponse N-Genius. Avec `Http::retry()`, c'est une `RequestException` qui remonte et la branche `! $response->successful()` (qui logge le body) n'est jamais atteinte.
- **Contournement local** : tunnel HTTPS public (ngrok / Cloudflare Tunnel / `laragon share`), mettre l'URL dans `APP_URL`, puis `php artisan config:clear` et ouvrir le site via cette URL.
- **Pistes** : passer `throw: false` à `retry()` (ou capturer `RequestException` et logger `$e->response->json()`) ; vérifier que `app:check-payment` ne reste pas bloqué sur des commandes sans retour.
- **Correctif local requis pour le tunnel (testé le 2026-11-04, non commité)** : avec `cloudflared ... --http-host-header dimancheabamako.test`, Laravel construit `asset()` et `route()` depuis l'hôte reçu (`http://dimancheabamako.test/...`) et non depuis `APP_URL` → CSS/images cassés et URLs de retour N-Genius refusées. Dans `AppServiceProvider::boot()` :
  ```php
  if (app()->isLocal()) {
      URL::forceRootUrl(config('app.url'));
      URL::forceScheme(parse_url((string) config('app.url'), PHP_URL_SCHEME) ?: 'http');
  }
  ```
  `forceScheme` est nécessaire car le tunnel parle HTTP en interne (sinon `http://` malgré `APP_URL=https://`). Avec ce correctif : panier → validation → redirection vers `paypage.sandbox.orabankml.ngenius-payments.com` OK. Reste non testé : saisie de carte sandbox et retour sur `order.validate` (état `PURCHASED`).
- **Piste** : décider si ce bloc entre définitivement dans le code (local uniquement) ou si on documente seulement la procédure tunnel.
