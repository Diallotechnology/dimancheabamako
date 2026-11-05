# Bugs hors périmètre

Bugs rencontrés pendant d'autres chantiers, notés ici pour être corrigés ensuite (un fix = un test si sécurité/finance, puis marquer l'entrée « ✅ RÉSOLU » sans la supprimer).

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

# Audit sécurité du 2026-11-04 (C1 et C2 corrigés avec tests : `tests/Feature/AccountTakeoverTest.php`)

## 2026-11-04 — I1 · Frais de livraison choisis par le client — ✅ RÉSOLU (2026-11-05)

- **Où** : `app/Service/OrderService.php` (`Shipping::findOrFail($request->integer('livraison'))`), `app/Http/Controllers/OrderController.php:store`.
- **Cause** : l'ID du tarif vient du formulaire et n'est jamais recoupé avec le pays, le transport ni le poids du panier ; `$order->shipping` et le montant envoyé à N-Genius en dépendent.
- **Symptôme** : en envoyant l'ID du tarif le moins cher, le client paie moins de frais de port que dû.
- **Piste** : recalculer côté serveur via `CartAction::getShippingCost($country_id, $transport_id)` et ignorer `livraison`. Test RED obligatoire (logique financière).
- **Résolution** : nouveau `app/Service/ShippingService::resolve()` (source unique, aussi utilisé par `CartAction::getShippingCost`) ; `OrderService::createOrder` recalcule le tarif (pays, transport, poids du panier via `CartService::getTotalWeight()`) et rejette la commande (`DomainException`) si `livraison` diffère ou s'il n'existe aucun tarif. Tests dans `tests/Feature/OrderServiceTest.php`.

## 2026-11-04 — I2 · `GET order/cancel` supprime n'importe quelle commande — ✅ RÉSOLU (2026-11-05)

- **Où** : `app/Http/Controllers/OrderController.php:cancel`, route `order/cancel`.
- **Cause** : suppression sur simple GET, sans contrôle de l'état (`PURCHASED` inclus) ni du propriétaire ; seule la référence est requise.
- **Symptôme** : une référence qui fuit (historique, Referer, URL de retour) permet d'effacer une commande déjà payée.
- **Piste** : ne supprimer que les commandes non payées, vérifier l'appartenance (session / client), ne plus faire d'action destructive en GET. Test RED obligatoire (financier + permissions).
- **Résolution** : `OrderController::store` mémorise la référence en session (`pending_order_ref`) ; `cancel` ne traite que cette référence, jamais une commande payée (`reference` ou `PURCHASED`), prend un `lockForUpdate`, annule d'abord le lien chez N-Genius et ne supprime la commande que si l'annulation est confirmée (sinon `app:check-payment` tranche). Référence inconnue ou refusée : même redirection, plus de 404. Tests dans `tests/Feature/OrderCancelTest.php`.
- **À surveiller en prod** : l'appel N-Genius se fait dans la transaction (`Http::retry` : verrou de ligne tenu jusqu'à ~30 s si N-Genius est lent). Cela peut bloquer `app:check-payment` sur cette ligne, et plusieurs annulations lentes en parallèle peuvent saturer les connexions DB. Pas corrigé volontairement ; piste si ça se produit : réduire timeout/retry sur `cancelPaymentLink`, ou passer à un état intermédiaire « annulation en cours » et appeler N-Genius hors transaction. Retour dans un autre navigateur que celui de la commande : rien n'est supprimé, `app:check-payment` purge l'impayé à expiration.

## 2026-11-04 — I3 · Upload de fichiers sans filtre de type — ✅ RÉSOLU (2026-11-05)

- **Où** : `app/Http/Requests/UpdateSlideRequest.php` (`'image' => 'nullable'`), `app/Http/Controllers/ImageController.php:update` (`'chemin' => 'required|file'`).
- **Cause** : aucun contrôle de MIME ni de taille ; `hashName()` déduit l'extension du contenu et le fichier est stocké sur le disque `public`.
- **Symptôme** : un fichier `.php` ou `.html` déposé via l'admin devient accessible depuis le web (exécution de code / XSS stocké). Combiné à un compte admin compromis, risque maximal.
- **Piste** : `['image', 'mimes:jpg,jpeg,png,webp', 'max:5120']` comme dans `StoreProductRequest`. Test RED (sécurité).
- **Résolution** : `image|mimes:jpg,jpeg,png,webp|max:5120` sur `UpdateSlideRequest` et `ImageController::update` ; tests dans `tests/Feature/AdminUploadValidationTest.php`.

## 2026-11-04 — I4 · Login du panier sans les protections du login principal — ✅ RÉSOLU (2026-11-05)

- **Où** : `app/Livewire/Panier.php:login_submit`.
- **Cause** : `Auth::attempt` direct, sans `RateLimiter` (contrairement à `LoginRequest`), sans `session()->regenerate()`, et sans le contrôle `change_password`.
- **Symptôme** : brute-force possible sans limite, fixation de session, un compte au mot de passe initial peut se connecter sans le changer.
- **Piste** : factoriser la logique de `LoginRequest::authenticate()` et la réutiliser ici. Test RED (sécurité).
- **Correction de l'audit** : le point « pas de `session()->regenerate()` / fixation de session » était un **faux positif** : `Auth::attempt()` → `SessionGuard::login()` → `updateSession()` régénère déjà la session. Les deux autres points (pas de limitation de tentatives, pas de contrôle `change_password`) étaient réels.
- **Résolution** : nouveau `app/Service/LoginService` (limitation 5 tentatives sur la clé `email|ip`, tentative d'authentification, URL signée de changement de mot de passe) utilisé par `LoginRequest`, `AuthenticatedSessionController` et `Panier::login_submit`. Le compteur est partagé entre les deux formulaires. Les erreurs du panier s'affichent maintenant sous le champ e-mail. Tests dans `tests/Feature/PanierLoginTest.php`.

## 2026-11-04 — I5 · Données personnelles dans les logs N-Genius

- **Où** : `app/Helper/OrderAPI.php:39` (`'response' => $data`) et `:53-56` (`'order' => $order`) ; `app/Http/Controllers/PayLinkController.php:74-77` (`getTraceAsString()`).
- **Cause** : le payload complet de la commande (email, adresse de facturation, nom du porteur, PAN masqué) est écrit tel quel dans `laravel.log`.
- **Symptôme** : données personnelles et de paiement en clair dans les logs.
- **Piste** : ne logger que `reference`, `state` et le statut HTTP. À coordonner avec l'entrée « Paiement N-Genius invisible » ci-dessus (logger le corps d'erreur utile sans fuite de données).

## 2026-11-04 — M1 · Actions sensibles déclenchées en GET

- **Où** : `routes/web.php` — `product/favoris/{data}/{product_id}`, `paylink/{paylink}/regenerate`, `admin/maintenance` (`AdminController::maintenance`).
- **Cause** : actions qui modifient l'état exposées en GET, donc sans protection CSRF effective.
- **Symptôme** : un lien piégé ouvert par un admin bascule le mode maintenance, régénère un lien de paiement ou change un favori.
- **Piste** : passer en POST/PUT avec jeton CSRF (Inertia `router.post`). `favoris_update` fait aussi `Product::find()->update()` sans 404 si l'ID n'existe pas.

## 2026-11-04 — M2 · Prix et stock figés dans le panier (cache 24 h)

- **Où** : `app/Service/CartService.php` (TTL 1440 min), `app/Service/OrderService.php` (contrôle de stock sur les valeurs du cache).
- **Cause** : prix, stock et poids sont copiés au moment de l'ajout et jamais revalidés avec la base.
- **Symptôme** : promo expirée, hausse de prix ou rupture de stock non répercutées ; la commande peut être payée à l'ancien prix.
- **Piste** : relire prix/stock depuis `Product` dans `createOrder` (dans la transaction, avec verrou). Test RED (financier + race condition).

## 2026-11-04 — M3 · Validation trop permissive

- **Où** : `StoreUserRequest` / `UpdateUserRequest` (`role` libre), `UpdateOrderRequest` (`etat` libre), `StoreProductRequest` / `UpdateProductRequest` (`prix`, `stock` sans `min:0`), `StorePayLinkRequest` (`montant` sans `min:1`), `StoreOrderRequest` (`password` sans règle de robustesse).
- **Cause** : règles `required` / `nullable` sans contrainte de valeur.
- **Symptôme** : rôle invalide → erreur 500 ; état de commande arbitraire ; prix, stock ou montant négatifs acceptés.
- **Piste** : `Rule::enum(RoleEnum::class)`, `Rule::enum(OrderEnum::class)`, `min:0` / `min:1`, `Rules\Password::defaults()`.

## 2026-11-04 — M4 · Session non régénérée à la confirmation d'inscription — ❌ FAUX POSITIF (2026-11-05)

- **Où** : `app/Http/Controllers/PendingRegistrationController.php` (`Auth::login($user)`).
- **Cause** : pas de `session()->regenerate()` après la connexion automatique.
- **Symptôme** : fixation de session possible ; `mergeGuestCartToUser` utilise l'ID de session d'avant connexion.
- **Piste** : régénérer la session après avoir récupéré `$oldSessionId`.
- **Vérification** : `Auth::login()` appelle `SessionGuard::updateSession()` qui fait `session->regenerate(true)`. La session est bien régénérée ; rien à corriger. Seul reste vrai le point secondaire : `mergeGuestCartToUser` doit recevoir l'ancien ID de session, ce que le code fait déjà (`$oldSessionId` capturé avant `Auth::login`).

## 2026-11-04 — M5 · Fichiers jamais supprimés du disque

- **Où** : `app/Http/Controllers/SlideController.php:destroy` (`file_delete` après le `return`, jamais exécuté), `app/Helper/DeleteAction.php:file_delete` (`public_path(Storage::url(...))`).
- **Cause** : code mort après `return` ; `Storage::url()` renvoie `/storage/...`, qui ne correspond pas au vrai chemin sur disque.
- **Symptôme** : les fichiers supprimés (slides, images produit) restent accessibles sur le serveur.
- **Piste** : utiliser `deleteStored()` (disque `public`) partout et supprimer `file_delete` / `file_multiple_delete`.

## 2026-11-04 — M6 · Pas de limitation sur la création de commande

- **Où** : `routes/web.php` (`POST order`), `app/Http/Controllers/OrderController.php:store`.
- **Cause** : aucun `throttle`, et chaque appel crée un client, une commande et un ordre N-Genius.
- **Symptôme** : spam de commandes et d'appels à l'API de paiement ; la facture `order/invoice/{trans_ref}` est publique et contient des données personnelles.
- **Piste** : `throttle:10,1` sur `POST order`, protéger `order/invoice` (URL signée ou propriétaire).

## 2026-11-04 — M7 · Valeurs de configuration risquées par défaut

- **Où** : `.env.example` (`APP_DEBUG=true`), `config/session.php` (`'secure' => env('SESSION_SECURE_COOKIE')` sans défaut).
- **Cause** : le gabarit d'environnement encourage le debug activé et un cookie de session sans drapeau `Secure`.
- **Symptôme** : si copié tel quel en production, stack traces exposées et cookie envoyé en HTTP.
- **Piste** : vérifier le `.env` de production (`APP_DEBUG=false`, `SESSION_SECURE_COOKIE=true`) ; mettre `APP_DEBUG=false` dans `.env.example`.

## 2026-11-05 — Poids de la commande toujours à `0 Kg`

- **Où** : `app/Service/OrderService.php` (`$totalWeight = $cart->getContent()->pluck('attributes.poids')->sum()`, puis `'poids' => $totalWeight.' Kg'`).
- **Cause** : `poids` est une clé de premier niveau de chaque ligne du panier, pas dans `attributes` (qui ne contient que `cover` et `reduction`). Le `pluck` ne trouve rien et, en plus, ne multiplie pas par la quantité.
- **Symptôme** : la colonne `orders.poids` vaut `0 Kg` pour toutes les commandes (à confirmer en base), donc le poids affiché dans l'admin et sur la facture est faux.
- **Piste** : utiliser `$cart->getTotalWeight()` (ajouté pour I1). Pas de test RED obligatoire (affichage), mais un test dans `OrderServiceTest` est peu coûteux.

## 2026-11-05 — `OrderController::valid()` vide le panier sans vérifier de paiement

- **Où** : `app/Http/Controllers/OrderController.php:valid` (route `GET order/validate`).
- **Cause** : la méthode vide le panier à chaque appel, sans vérifier qu'une commande a été payée ; la ligne `
edirect()->to(...)` dont le résultat est jeté est du code mort.
- **Symptôme** : n'importe qui peut vider son propre panier en ouvrant `/order/validate` (sans gravité de sécurité) ; la page « validée » s'affiche sans preuve de paiement.
- **Piste** : ne vider le panier et n'afficher la confirmation que si la référence de session `pending_order_ref` correspond à une commande `PURCHASED` ; supprimer la ligne morte.
