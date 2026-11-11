# Bugs hors périmètre

Bugs rencontrés pendant d'autres chantiers, notés ici pour être corrigés ensuite (un fix = un test si sécurité/finance, puis marquer l'entrée « ✅ RÉSOLU » sans la supprimer).

## 2026-11-04 — Paiement N-Genius invisible en cas d'échec de création d'ordre — ✅ RÉSOLU (2026-11-05)

> Validé en local via tunnel : commande n°5 payée en sandbox (Visa sans 3DS), `PURCHASED` chez N-Genius, puis `app:check-payment` → `trans_state=PURCHASED`, `reference=DAB2026-1`. Note : la Visa 3DS 1.0 `4012001037141112` est refusée sur cet outlet (3DS « Not enrolled »), ce n'est pas un bug applicatif.

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
- **Décision** : le bloc `forceRootUrl`/`forceScheme` (local uniquement) est dans `AppServiceProvider::boot()`.
- **Corrigé le 2026-11-05 (non commité)** : `createOrder` utilise `retry(..., throw: false)` ; le statut et le body N-Genius sont loggés (`NGENIUS: Create order failed`), l'exception porte le code HTTP, `Order failed rollback` logge classe + origine. Tests : `tests/Feature/NgeniusCreateOrderFailureTest.php`.
- **Reste à faire** : valider en local (carte sandbox + retour `order.validate` → `PURCHASED`), puis retirer cette entrée.

## 2026-11-05 — Body N-Genius jamais loggé sur les appels via `api()` (à traiter avec son propre test) — ✅ RÉSOLU (2026-11-05)

> Résolution : `api()` et `getAccessToken()` utilisent `retry(..., throw: false)` ; les 4 logs d'erreur passent par `ngeniusErrorContext()` (statut + body). Les appelants (`CheckPayment`, `DeleteOrderPaymentExpire`, `CheckPayByLink`, `DeletePayByLink`, `PayLinkController`, `OrderController`) catchent tous `Exception`/`Throwable` : aucun ne dépendait de `RequestException`, le contrat « ça lève » est inchangé (seuls type et message changent). Test : `tests/Feature/NgeniusApiFailureLogTest.php`.

- **Où** : `app/Helper/OrderAPI.php` — `getOrderStatus`, `cancelPayment`, `cancelPaymentLink`, tous via `api()` (`retry(2, 200)` sans `throw: false`).
- **Constat** : en cas d'échec HTTP, `retry()` lève une `RequestException` avant la branche `! $response->successful()` : les `Log::error(... 'body' => ...)` de ces trois méthodes ne sont jamais atteints (code mort), comme pour `createOrder` avant correctif.
- **Piste** : même traitement que `createOrder` (`throw: false`), mais le contrat de l'exception change (type et message) : adapter les appelants qui catchent (`app:check-payment`, `OrderController::cancel`, etc.) et les vérifier un par un.
- **Statut** : non corrigé volontairement, hors périmètre du chantier `createOrder`. À faire avec son propre test.

## 2026-11-05 — `order.validate` affiche « Commande effectuée avec succès » même si le paiement a échoué — ✅ RÉSOLU (2026-11-05)

> Résolution : `valid()` compare `?ref=` à `session('pending_order_ref')` (retour sandbox réel vérifié : N-Genius envoie bien `?ref=<trans_ref>`), interroge N-Genius et affiche succès (`PURCHASED`, panier vidé) / échec (`FAILED|CANCELLED|EXPIRED`, panier conservé) / en cours de vérification (ref absente ou autre session, erreur API, état inconnu). Tests : `tests/Feature/OrderValidateTest.php`.

- **Où** : `app/Http/Controllers/OrderController.php:130-137` (`valid()`).
- **Constat** (test sandbox, commande n°3) : N-Genius redirige vers `redirectUrl` (`order.validate`) quel que soit le résultat du paiement. Paiement `FAILED` (3DS « Not enrolled »), mais la page « Opération terminée / Votre commande a été effectuée avec succès » s'affiche et le panier est vidé. `valid()` ne lit pas l'état du paiement ; le `redirect()->to(...)` est jeté (code mort).
- **Impact** : message trompeur pour le client (il croit avoir payé) ; commande restée impayée jusqu'à `app:check-payment`. Aucun risque de marquer `PURCHASED` à tort (c'est le scheduler qui tranche).
- **Piste** : interroger `getOrderStatus` (ou la référence en session `pending_order_ref`) dans `valid()` et afficher succès / échec / « en cours de vérification » selon l'état ; ne vider le panier qu'en cas de succès. Logique financière : RED test d'abord.

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

## 2026-11-04 — I5 · Données personnelles dans les logs N-Genius — ✅ RÉSOLU (2026-11-05)

- **Où** : `app/Helper/OrderAPI.php:39` (`'response' => $data`) et `:53-56` (`'order' => $order`) ; `app/Http/Controllers/PayLinkController.php:74-77` (`getTraceAsString()`).
- **Cause** : le payload complet de la commande (email, adresse de facturation, nom du porteur, PAN masqué) est écrit tel quel dans `laravel.log`.
- **Symptôme** : données personnelles et de paiement en clair dans les logs.
- **Piste** : ne logger que `reference`, `state` et le statut HTTP. À coordonner avec l'entrée « Paiement N-Genius invisible » ci-dessus (logger le corps d'erreur utile sans fuite de données).
- **Résolution** : `OrderAPI::ngeniusLogContext()` ne garde que `reference`, les états de paiement et les clés de premier niveau ; utilisé dans `OrderAPI` (commande sans bloc paiement, annulation sans référence de paiement) et `CheckPayment` (« Payment state missing »). `PayLinkController::store` ne logue plus la trace complète. Tests dans `tests/Feature/NgeniusLogPrivacyTest.php`.
- **Volontairement conservé** : les corps de réponses d'erreur N-Genius (`'body' => $response->json()`), utiles au diagnostic (cf. entrée « Paiement N-Genius invisible ») et qui ne contiennent pas le payload client. À revérifier si N-Genius y renvoie un jour des données de commande. Les jobs mail loguent `$exception->getMessage()` : un message d'erreur SMTP peut contenir une adresse e-mail, non traité ici.

## 2026-11-04 — M1 · Actions sensibles déclenchées en GET — ✅ RÉSOLU (2026-11-05)

- **Où** : `routes/web.php` — `product/favoris/{data}/{product_id}`, `paylink/{paylink}/regenerate`, `admin/maintenance` (`AdminController::maintenance`).
- **Cause** : actions qui modifient l'état exposées en GET, donc sans protection CSRF effective.
- **Symptôme** : un lien piégé ouvert par un admin bascule le mode maintenance, régénère un lien de paiement ou change un favori.
- **Piste** : passer en POST/PUT avec jeton CSRF (Inertia `router.post`). `favoris_update` fait aussi `Product::find()->update()` sans 404 si l'ID n'existe pas.
- **Résolution** : `product.favori` passe en `PUT` (`whereIn('data', ['0','1'])`, `findOrFail` donc 404 si le produit n'existe pas, réponse 204), `paylink.regenerate` et `maintenance` en `POST`. Front adapté (`axios.put`, `<Link method="post" as="button">`) ; `npm run build` passe. Tests dans `tests/Feature/StateChangingRoutesTest.php` (méthodes des routes vérifiées sans les exécuter : un GET sur `maintenance` mettrait vraiment l'application en maintenance).
- **À vérifier à la main** : l'apparence du lien « Mode maintenance » du menu (maintenant rendu comme un bouton) et le clic sur chaque action dans le navigateur.

## 2026-11-04 — M2 · Prix et stock figés dans le panier (cache 24 h) — ✅ RÉSOLU (2026-11-05)

- **Où** : `app/Service/CartService.php` (TTL 1440 min), `app/Service/OrderService.php` (contrôle de stock sur les valeurs du cache).
- **Cause** : prix, stock et poids sont copiés au moment de l'ajout et jamais revalidés avec la base.
- **Symptôme** : promo expirée, hausse de prix ou rupture de stock non répercutées ; la commande peut être payée à l'ancien prix.
- **Piste** : relire prix/stock depuis `Product` dans `createOrder` (dans la transaction, avec verrou). Test RED (financier + race condition).
- **Résolution** : `OrderService::createOrder` relit les produits du panier en début de transaction (`lockForUpdate`, promotions actives) puis appelle `CartService::refreshFromProducts` (prix promo compris, stock, poids, précommande ; lignes orphelines retirées, quantités inchangées) avant tout calcul de poids/livraison. Si un prix a changé ou si un produit a disparu, ou si le stock est insuffisant, `StaleCartException` est levée : aucune commande créée, panier déjà resynchronisé, message `messages.panier.price_changed` (fr/en) via `OrderController::store`, qui lit ensuite le total N-Genius dans le panier à jour. Tests dans `tests/Feature/OrderServiceTest.php` (fixtures des anciens tests alignées sur le prix et le poids du produit). `Product::activePromotion()` reçoit son type de retour, ce qui retire deux entrées obsolètes de `phpstan-baseline.neon`.
- **Limites connues** : (1) le stock n'est pas réservé à la commande, il n'est décrémenté qu'au paiement (`CheckPayment`) ; deux clients peuvent payer le dernier article, la commande passe alors en « Stock épuisé » sans remboursement automatique, à traiter séparément. (2) `lockForUpdate` n'est pas testable en SQLite mémoire : la course réelle n'est pas couverte par un test.

## 2026-11-04 — M3 · Validation trop permissive — ✅ RÉSOLU (2026-11-05)

- **Où** : `StoreUserRequest` / `UpdateUserRequest` (`role` libre), `UpdateOrderRequest` (`etat` libre), `StoreProductRequest` / `UpdateProductRequest` (`prix`, `stock` sans `min:0`), `StorePayLinkRequest` (`montant` sans `min:1`), `StoreOrderRequest` (`password` sans règle de robustesse).
- **Cause** : règles `required` / `nullable` sans contrainte de valeur.
- **Symptôme** : rôle invalide → erreur 500 ; état de commande arbitraire ; prix, stock ou montant négatifs acceptés.
- **Piste** : `Rule::enum(RoleEnum::class)`, `Rule::enum(OrderEnum::class)`, `min:0` / `min:1`, `Rules\Password::defaults()`.
- **Résolution** : `Rule::enum(RoleEnum)` (création et modification d'utilisateur), `Rule::enum(OrderEnum)` pour `etat`, `min:0` sur `prix`, `stock` et le montant de livraison, `min:1` sur le montant d'un paylink, `Rules\Password::defaults()` sur le mot de passe du checkout. **Ajout hors entrée d'origine** : `reduction` d'une promotion bornée à 1–100 (au-dessus de 100 le prix devient négatif). Tests dans `tests/Feature/AdminValidationRulesTest.php`.

## 2026-11-04 — M4 · Session non régénérée à la confirmation d'inscription — ❌ FAUX POSITIF (2026-11-05)

- **Où** : `app/Http/Controllers/PendingRegistrationController.php` (`Auth::login($user)`).
- **Cause** : pas de `session()->regenerate()` après la connexion automatique.
- **Symptôme** : fixation de session possible ; `mergeGuestCartToUser` utilise l'ID de session d'avant connexion.
- **Piste** : régénérer la session après avoir récupéré `$oldSessionId`.
- **Vérification** : `Auth::login()` appelle `SessionGuard::updateSession()` qui fait `session->regenerate(true)`. La session est bien régénérée ; rien à corriger. Seul reste vrai le point secondaire : `mergeGuestCartToUser` doit recevoir l'ancien ID de session, ce que le code fait déjà (`$oldSessionId` capturé avant `Auth::login`).

## 2026-11-04 — M5 · Fichiers jamais supprimés du disque — ✅ RÉSOLU (2026-11-05)

- **Où** : `app/Http/Controllers/SlideController.php:destroy` (`file_delete` après le `return`, jamais exécuté), `app/Helper/DeleteAction.php:file_delete` (`public_path(Storage::url(...))`).
- **Cause** : code mort après `return` ; `Storage::url()` renvoie `/storage/...`, qui ne correspond pas au vrai chemin sur disque.
- **Symptôme** : les fichiers supprimés (slides, images produit) restent accessibles sur le serveur.
- **Piste** : utiliser `deleteStored()` (disque `public`) partout et supprimer `file_delete` / `file_multiple_delete`.
- **Nuance** : l'affirmation « chemin qui ne correspond pas au fichier réel » était trop forte : avec le lien `public/storage`, `file_delete` pouvait fonctionner en production. Le défaut certain était le code mort de `SlideController::destroy` ; le reste était fragile et non testable.
- **Résolution** : `SlideController` et `ImageController` utilisent `deleteStored()` (disque `public`, comme `ProductController`) ; `file_delete` et `file_multiple_delete` supprimés de `DeleteAction`. Tests dans `tests/Feature/AdminUploadValidationTest.php`.

## 2026-11-04 — M6 · Pas de limitation sur la création de commande — ✅ RÉSOLU (2026-11-05)

- **Où** : `routes/web.php` (`POST order`), `app/Http/Controllers/OrderController.php:store`.
- **Cause** : aucun `throttle`, et chaque appel crée un client, une commande et un ordre N-Genius.
- **Symptôme** : spam de commandes et d'appels à l'API de paiement ; la facture `order/invoice/{trans_ref}` est publique et contient des données personnelles.
- **Piste** : `throttle:10,1` sur `POST order`, protéger `order/invoice` (URL signée ou propriétaire).
- **Résolution** : `throttle:10,1` sur `POST order`, `throttle:30,1` sur `GET order/invoice/{id}`. Tests dans `tests/Feature/PublicRoutesThrottleTest.php`.
- **Résolution (facture)** : la facture n'était pas énumérable (`trans_ref` est un UUID N-Genius ; `reference` séquentielle n'est qu'affichée), mais le lien, jamais expiré et diffusé par e-mail, URL de retour N-Genius et facture elle-même, donnait accès aux données personnelles du client à quiconque le détenait. Nouvelle route `order/facture/{id}` (`order.invoice.signed`, middleware `signed` + `throttle:30,1`) ; `OrderMail` génère désormais `URL::signedRoute(...)` (signature permanente, sans expiration). L'ancienne route `order.invoice` ne sert plus que les commandes créées avant `config('invoice.legacy_cutoff')` (`INVOICE_LEGACY_CUTOFF`, défaut 2026-11-06) pour ne pas casser les e-mails déjà envoyés. Tests dans `tests/Feature/InvoiceAccessTest.php`.
- **À faire plus tard** : supprimer la route `order.invoice`, `config/invoice.php` et `INVOICE_LEGACY_CUTOFF` quand la coexistence n'est plus utile. **Prérequis prod** : `APP_KEY` stable (une rotation invalide toutes les URLs signées) ; ajouter `INVOICE_LEGACY_CUTOFF` au `.env` réel (ou garder le défaut) et relancer `config:cache` si utilisé. Les e-mails déjà en file au déploiement gardent l'ancien lien, couvert par la coexistence. Pas de page « renvoyer ma facture » (hors périmètre).

## 2026-11-04 — M7 · Valeurs de configuration risquées par défaut — ✅ RÉSOLU (2026-11-05) côté dépôt

- **Où** : `.env.example` (`APP_DEBUG=true`), `config/session.php` (`'secure' => env('SESSION_SECURE_COOKIE')` sans défaut).
- **Cause** : le gabarit d'environnement encourage le debug activé et un cookie de session sans drapeau `Secure`.
- **Symptôme** : si copié tel quel en production, stack traces exposées et cookie envoyé en HTTP.
- **Piste** : vérifier le `.env` de production (`APP_DEBUG=false`, `SESSION_SECURE_COOKIE=true`) ; mettre `APP_DEBUG=false` dans `.env.example`.
- **Résolution** : `.env.example` avec `APP_DEBUG=false` ; `config/session.php` : `secure` vaut `true` par défaut quand `APP_ENV=production` (surchargeable par `SESSION_SECURE_COOKIE`). Tests dans `tests/Feature/SafeConfigDefaultsTest.php`.
- **Reste manuel** : vérifier le `.env` réel de production (`APP_DEBUG=false`, `APP_ENV=production`), que le dépôt ne peut pas contrôler.

## 2026-11-05 — Poids de la commande toujours à `0 Kg` — ✅ RÉSOLU (2026-11-05)

- **Où** : `app/Service/OrderService.php` (`$totalWeight = $cart->getContent()->pluck('attributes.poids')->sum()`, puis `'poids' => $totalWeight.' Kg'`).
- **Cause** : `poids` est une clé de premier niveau de chaque ligne du panier, pas dans `attributes` (qui ne contient que `cover` et `reduction`). Le `pluck` ne trouve rien et, en plus, ne multiplie pas par la quantité.
- **Symptôme** : la colonne `orders.poids` vaut `0 Kg` pour toutes les commandes (à confirmer en base), donc le poids affiché dans l'admin et sur la facture est faux.
- **Piste** : utiliser `$cart->getTotalWeight()` (ajouté pour I1). Pas de test RED obligatoire (affichage), mais un test dans `OrderServiceTest` est peu coûteux.
- **Résolution** : `OrderService::createOrder` utilise `$cart->getTotalWeight()` (poids × quantité). Assertion `poids` = `4.5 Kg` ajoutée au test de création de commande dans `tests/Feature/OrderServiceTest.php`. ✅ RÉSOLU (2026-11-05)

## 2026-11-05 — `OrderController::valid()` vide le panier sans vérifier de paiement — ✅ RÉSOLU (2026-11-05)

> Doublon de l'entrée « `order.validate` affiche « Commande effectuée avec succès » même si le paiement a échoué » (plus haut), corrigé par le même changement : voir sa résolution et `tests/Feature/OrderValidateTest.php`.

- **Où** : `app/Http/Controllers/OrderController.php:valid` (route `GET order/validate`).
- **Cause** : la méthode vide le panier à chaque appel, sans vérifier qu'une commande a été payée ; la ligne `
edirect()->to(...)` dont le résultat est jeté est du code mort.
- **Symptôme** : n'importe qui peut vider son propre panier en ouvrant `/order/validate` (sans gravité de sécurité) ; la page « validée » s'affiche sans preuve de paiement.
- **Piste** : ne vider le panier et n'afficher la confirmation que si la référence de session `pending_order_ref` correspond à une commande `PURCHASED` ; supprimer la ligne morte.

## 2026-11-05 — Écriture admin du stock = valeur absolue (écrase les réservations) — ✅ RÉSOLU (2026-11-05)
Hors périmètre de la réservation de stock. `ProductController@update` (`$product->update($data)`) et `store` écrivent `stock` tel que saisi dans le formulaire. Une édition admin pendant des commandes en cours écrase les décréments/restitutions faits entre le chargement de la page et l'envoi (lost update) : avec la réservation à la commande, le stock réservé peut être « rendu » par erreur. À traiter séparément (delta, ou verrou + rechargement).

> Résolution : `UpdateProductRequest` exige `stock_initial` (stock affiché à l'ouverture du formulaire) ; `ProductController@update` applique, sous transaction et `lockForUpdate`, `stock réel + (stock saisi − stock_initial)` et refuse (erreur sur `stock`) un résultat négatif. `Update.vue` envoie `stock_initial` (resynchronisé après succès). La création (`store`) est inchangée. Tests : `tests/Feature/AdminProductStockTest.php`.

## 2026-11-05 — Notification `STOCK` renvoyée chaque minute — ✅ RÉSOLU (2026-11-05)
Hors périmètre. Une commande PURCHASED passée en `etat = STOCK` garde `reference` null et reste dans la sélection de `app:check-payment` : la notification `NotifOrderStockIssue` repart à chaque passage, sans mail client ni remboursement. Avec la réservation, le cas ne devrait plus se produire (filet `stock < 0` seulement), mais le comportement n'a pas été refondu.

> Résolution : `app:check-payment` ignore les commandes déjà en `etat = STOCK` : une seule alerte, la commande payée reste sans référence ni mail client jusqu'au traitement manuel (réassort ou remboursement N-Genius). **Attention** : repasser une telle commande à un autre `etat` la fait reprendre par `check-payment` comme une commande `PURCHASED` normale. Test : `tests/Feature/StockReservationTest.php`.

## 2026-11-05 — Déploiement de la réservation de stock
Avant de déployer : vérifier que `Order` avec `reference IS NULL AND trans_ref IS NOT NULL` = 0 ligne. Les commandes en cours créées avant la bascule n'ont pas réservé de stock : `check-payment` ne décrémente plus et `delete-order-payment-expire` / annulation restitueraient un stock jamais pris.

## 2026-11-05 — Limitation de test : `order.validate` non couvert pour un invité

- **Où** : `tests/Feature/OrderValidateTest.php`.
- **Constat** : les tests utilisent un acheteur connecté (clé de panier `cart_user_{id}`), car l'id de session d'un invité change pendant la requête de test et casse la clé `cart_guest_{sessionId}` seedée avant. `valid()` n'a aucune branche spécifique aux invités (même mapping d'état), donc le risque est faible.
- **Piste** : couvrir le cas invité (panier vidé / conservé) en seedant le panier via `CartService` pendant la même requête, ou en fixant l'id de session de test.
- **Statut** : non bloquant, à combler plus tard.

# Audit sécurité auth du 2026-11-06 (login principal, login panier, inscription différée) — constats, aucun correctif appliqué

> Vérifié sans problème : régénération de session (`Auth::attempt`/`Auth::login` → `updateSession`), logout (`invalidate()` + rotation du `remember_token` par `Auth::logout()`), compteur de tentatives partagé entre les deux formulaires de login, `change_password` contrôlé aux deux points d'entrée, jeton d'inscription à usage unique (supprimé dans la transaction) avec expiration 1 h et purge horaire, `RoleMiddleware` (`default => false`), `Rule::enum` sur les rôles, aucun mot de passe ni jeton dans les logs, cookies de session (`http_only`, `lax`, `secure` en production).

## 2026-11-06 — A1 · Compte créé au checkout pour n'importe quel e-mail, sans preuve de possession — ✅ RÉSOLU (2026-11-06)
> Résolution : le checkout ne crée plus de `User`. Un mot de passe saisi ouvre une `PendingRegistration` via le nouveau `app/Service/PendingRegistrationService` (mot de passe haché, jeton 64 car., 1 h) et le lien `ConfirmRegistrationMail` part après le commit (`DB::afterCommit`). Une inscription déjà en attente pour l'e-mail n'est ni écrasée ni re-jetonnée (le lien est renvoyé) ; un téléphone tenu par une autre inscription en attente est ignoré ; la commande n'est jamais bloquée. `StoreOrderRequest` : `NotDisposableEmail` (si mot de passe) et `max:255`. Tests : `tests/Feature/CheckoutAccountTest.php`. **UX faite** : `OrderController::store` pose `account_confirmation_pending` en session si une inscription est en attente ; `validate.blade.php` affiche `messages.account_confirmation_pending` (succès ou vérification en cours, pas en cas d'échec). UI pure, sans test.

- **Où** : `app/Service/OrderService.php` (bloc `if ($request->filled('password'))`), `app/Http/Requests/StoreOrderRequest.php` (`email` : `required|string|email` seulement).
- **Cause** : un invité peut fournir l'e-mail d'un tiers + un mot de passe de son choix ; si aucun `User` n'existe pour cet e-mail, le compte est créé et rattaché au `Client` trouvé par contact **ou** e-mail. Pas de confirmation par e-mail, pas de `NotDisposableEmail`, pas de `lowercase`/`max:255`.
- **Symptôme** : pré-détournement de compte : l'attaquant détient le compte lié à l'e-mail de la victime (et à son `Client` : e-mail, contact, pays visibles dans `profil`). Quand la victime s'inscrit ensuite, `unique:users,email` la bloque.
- **Piste** : ne pas créer de `User` au checkout (ou passer par `PendingRegistration` avec jeton e-mail) ; sinon au minimum `NotDisposableEmail`, `lowercase`, `max:255`. Test RED (sécurité).
- **TODO UX** : après une commande avec mot de passe saisi, afficher sur la page de confirmation de commande (et/ou dans le flash de retour) un message du type « Un e-mail vous a été envoyé : cliquez le lien pour activer votre compte », fr + en. Sans lui, le client ignore que son compte n'existe pas encore.

## 2026-11-06 — A2 · `ClientResolverService` rattache un compte à un `Client` existant via le téléphone seul — ✅ RÉSOLU (2026-11-06)
> Résolution : `ClientResolverService::resolve()` met à jour un `Client` seulement si l'e-mail correspond ; un téléphone connu avec un autre e-mail renvoie le `Client` inchangé. `isConfirmed()` conditionne la création/le rattachement d'un `User` (`OrderService`) et la confirmation d'inscription (`PendingRegistrationController` : jeton consommé, pas de compte ni de connexion, retour sur `register` avec le message `messages.register_phone_conflict`). La commande invité reste possible. Résolution redondante retirée de `OrderService`. Tests : `tests/Feature/ClientResolverTest.php`.

- **Où** : `app/Service/ClientResolverService.php::resolve` (priorité `contact`), appelé par `OrderService` et `PendingRegistrationController`.
- **Cause** : le `Client` est retrouvé par numéro de téléphone sans preuve de possession, puis **mis à jour** (`prenom`, `nom`, `pays`) avec les valeurs saisies.
- **Symptôme** : en s'inscrivant (avec son propre e-mail, confirmé) ou en commandant avec le numéro d'une victime, l'attaquant (1) écrase nom/prénom/pays du `Client` de la victime, (2) obtient un compte lié à ce `Client` (e-mail/contact affichés dans `profil`, commandes rattachées au même `Client`). Cross-tenant : test RED obligatoire.
- **Piste** : ne mettre à jour un `Client` existant que si l'e-mail correspond aussi (ou s'il n'a aucun `User`) ; ne jamais écraser nom/pays sans correspondance ; sinon créer un `Client` distinct.

## 2026-11-06 — A3 · Réinscription en attente : le mot de passe de la victime est écrasé — ✅ RÉSOLU (2026-11-06)
> Résolution : `RegisteredUserController::store` passe par `PendingRegistrationService::request()` (code dupliqué supprimé). Une inscription déjà en attente pour l'e-mail garde son mot de passe, son jeton et ses données ; le lien existant est renvoyé. Un téléphone tenu par l'inscription en attente d'un autre e-mail renvoie l'erreur `contact` (`messages.register_phone_conflict`) sans rien modifier. Tests : `tests/Feature/RegistrationPendingTest.php`. **Effet de bord** : un utilisateur qui se réinscrit avec un autre mot de passe garde le premier ; il doit utiliser « mot de passe oublié » après confirmation.

- **Où** : `app/Http/Controllers/Auth/RegisteredUserController.php::store` (`where('email')->orWhere('contact')` puis `update($data)`).
- **Cause** : un second POST `register` avec le même e-mail (ou le même téléphone) remplace `password`, `token` et même `email` de l'inscription en attente d'un autre, sans preuve de possession.
- **Symptôme** : (a) avec l'e-mail de la victime + mot de passe de l'attaquant, le lien reçu par la victime crée un compte dont l'attaquant connaît le mot de passe ; (b) avec le téléphone de la victime + e-mail de l'attaquant, l'inscription en attente de la victime est détournée. Limité par `throttle:5,10`.
- **Piste** : matcher uniquement sur l'e-mail ; gérer la contrainte `unique` sur `contact` par un message d'erreur, pas par écrasement. Test RED.

## 2026-11-06 — A4 · Profil : changement d'e-mail et de mot de passe sans mot de passe actuel — ✅ RÉSOLU (2026-11-06)
> Résolution : `UpdateUserProfilRequest` exige `current_password` (règle `current_password`) dès que l'e-mail change ou qu'un mot de passe est saisi ; le nom seul reste libre. `Auth::logoutOtherDevices()` après changement de mot de passe (profil et `PasswordController`), avec le middleware `auth.session` ajouté aux trois groupes `auth` (`routes/auth.php` ×2, groupe `admin` de `routes/web.php`) : sans lui l'appel n'aurait aucun effet. `AuthenticateSession::redirectUsing` renvoie vers `login` (sinon 401). Champ « Mot de passe actuel » dans `profil.blade.php`. Tests : `tests/Feature/ProfileSecurityTest.php`. **À vérifier à la main** : le formulaire de profil dans le navigateur, et qu'un reset de mot de passe par e-mail déconnecte bien les autres appareils.

- **Où** : `AuthenticatedSessionController::update` + `UpdateUserProfilRequest` (route `profil.update`).
- **Cause** : aucun `current_password` exigé (contrairement à `PasswordController::update`) ; l'e-mail change sans re-vérification ; les autres sessions ne sont pas invalidées.
- **Symptôme** : une session volée (poste partagé, XSS) suffit à prendre le compte durablement (nouveau mot de passe + nouvel e-mail pour le reset).
- **Piste** : exiger `current_password` pour toute modification d'e-mail/mot de passe ; `Auth::logoutOtherDevices` après changement. Test RED.

## 2026-11-06 — A5 · `Password::defaults()` jamais configuré (minimum 8 caractères seulement) — ✅ RÉSOLU (2026-11-06)
> Résolution : `Password::defaults()` configuré dans `AppServiceProvider::boot()` : 8 caractères minimum, lettres + chiffres (volontairement léger, décision du propriétaire ; ni casse mixte, ni vérification HIBP). Tests : `tests/Feature/PasswordRulesTest.php`.

- **Où** : `app/Providers/AppServiceProvider.php` (aucun `Password::defaults(...)`), utilisé par 6 requêtes (register, checkout, profil, reset, changement initial, `PasswordController`).
- **Cause** : sans configuration, `Rules\Password::defaults()` = `min(8)` ; la correction M3 s'appuie donc sur une règle quasi vide.
- **Piste** : `Password::defaults(fn () => Password::min(10)->letters()->mixedCase()->numbers()->uncompromised())` (`uncompromised` = appel réseau HIBP, à réserver à la production).

## 2026-11-06 — A6 · Énumération de comptes (inscription et mot de passe oublié) — ✅ RÉSOLU (2026-11-06) pour `forgot-password`
> Résolution : `PasswordResetLinkController::store` répond par le même message neutre (`messages.reset_link_neutral`) que l'e-mail existe ou non ; seul `RESET_THROTTLED` (indépendant du compte) reste signalé. `throttle:5,1` sur `POST forgot-password`. Tests : `tests/Feature/PasswordResetEnumerationTest.php`. **Décision** : `register` garde `unique:users,email` (énumération acceptée, compromis UX).

- **Où** : `StoreRegisterUserRequest` (`unique:users,email`), `PasswordResetLinkController::store` (`passwords.user` : « Aucun utilisateur n'a été trouvé »). `forgot-password` n'a pas de `throttle` de route (seul le throttle interne du broker, 60 s par e-mail).
- **Symptôme** : on peut savoir si un e-mail est client. Le login, lui, est uniforme (`auth.failed`) : OK.
- **Piste** : réponse identique « si ce compte existe, un e-mail a été envoyé » pour `forgot-password` + `throttle` de route. Pour `register`, accepter l'énumération (compromis UX) ou répondre de façon neutre.

## 2026-11-06 — A7 · Limitation de tentatives de login contournable ou retournable — ✅ RÉSOLU partiellement (2026-11-06)
> Résolution : `LoginService` ajoute une limite par IP seule (20 échecs/min, clé `login-ip|<ip>`, non remise à zéro par un login réussi), partagée par le login principal et celui du panier. Test : `tests/Feature/LoginIpThrottleTest.php`. **Reste manuel (prod)** : configurer `TrustProxies` selon l'infra réelle (points 2 et 3 ci-dessous), impossible à vérifier depuis le dépôt.

- **Où** : `app/Service/LoginService.php::throttleKey` (`email|ip`), `routes/auth.php` (`POST login` sans `throttle`), `TrustProxies::$proxies = null`.
- **Cause / symptôme** : (1) aucune limite par IP seule : 1 mot de passe sur N comptes (password spraying) n'atteint jamais 5 par clé ; (2) si la production est derrière un proxy/CDN non déclaré dans `$proxies`, `ip()` vaut l'IP du proxy : tous les clients partagent la clé par e-mail, donc 5 échecs volontaires verrouillent le compte de n'importe qui ; (3) si on déclare `'*'` sans proxy réel, `X-Forwarded-For` falsifiable contourne la limite.
- **Piste** : seconde clé par IP (ex. 20/min) ; configurer `trustProxies` selon l'infra réelle de production (à vérifier au déploiement).

## 2026-11-06 — A8 · Points faibles divers — ✅ partiellement traité (2026-11-06)
> Fait : `Panier::login_submit` vide `password` après un échec ; `AuthenticatedSessionController::store` utilise `Auth::user()` au lieu de relire par e-mail. **Fait aussi** : `confirm-registration` en GET (page + bouton) puis POST (`confirm-registration.store`, `throttle:10,1`), tests `ConfirmRegistrationPostTest.php`. **Régression corrigée** : double hachage du mot de passe à la confirmation (introduit par A1/A3 dans `fb244b8`, corrigé en `dfa4b56`) ; les comptes confirmés entre les deux ont un mot de passe inutilisable, à vérifier en prod (passer par « mot de passe oublié »). **Jeton haché (fait)** : `pending_registrations.token` stocke `sha256` du jeton (`PendingRegistration::hashToken`), le clair ne vit que dans l'e-mail ; une nouvelle demande pour le même e-mail régénère le jeton (ancien lien invalidé) sans toucher au mot de passe ; la migration `2026_11_06_120000_hash_pending_registration_tokens` hache les jetons existants (liens déjà envoyés toujours valables, irréversible). **Fait aussi** : à la confirmation, `email_verified_at` est renseigné et `change_password = true` (pas de second changement forcé), test dans `ConfirmRegistrationPostTest.php`. **Reste** :  `regenerateToken()` au logout (inoffensif).

- `confirm-registration/{token}` : action d'état + connexion en **GET** (cf. M1) ; un scanner de liens d'e-mail peut consommer le jeton avant l'utilisateur. Piste : page de confirmation + POST.
- `PendingRegistration.token` stocké en clair en base (préférer un hash) ; `email_verified_at` reste `null` après confirmation par lien.
- `AuthenticatedSessionController::destroy` n'appelle pas `regenerateToken()` (inoffensif : `invalidate()` vide la session et `start()` en recrée un).
- Comptes créés au checkout/inscription : `change_password = false` (défaut de la colonne), donc le premier login force un second changement de mot de passe. À arbitrer côté UX.
- `Panier::$password` est une propriété publique Livewire : elle transite dans le snapshot et n'est pas vidée après un échec. Piste : `$this->reset('password')`.
- `AuthenticatedSessionController::store` relit `User::where('email', $request->email)` au lieu d'utiliser l'utilisateur renvoyé par `attempt()` (sensibilité à la casse selon le collation).

## 2026-11-11 — Migration Poids/Devise/User : comptes et devises modifiables sans garde-fou

- **Où** : `routes/web.php:47` (`Route::resource('devise', …)->except('index', 'create', 'show', 'store')`), `app/Http/Controllers/DeviseController.php::destroy`, `app/Http/Requests/UpdateUserRequest.php:28`, `app/Http/Controllers/UserController.php::destroy` / `update`.
- **Constat** :
  1. La route `DELETE /admin/devise/{devise}` existe (et `DeviseController::destroy` supprime la ligne) alors que la page n'offre aucune suppression : supprimer `EUR` ou `CFA` casse la conversion de prix (`PriceService`, taux en cache).
  2. `UpdateUserRequest` déclare `role` en `nullable` : une requête `PATCH` avec `role: null` met `users.role` (colonne `string` non nullable) à `null` → erreur SQL (500) en MySQL strict. Non reproduit, déduit de la migration `create_users_table`.
  3. Aucun garde-fou sur `UserController::destroy` / `update` : un administrateur peut supprimer son propre compte ou se rétrograder, y compris le dernier administrateur.
- **Piste** : retirer `destroy` de la resource `devise` ; `role` en `required` dans `UpdateUserRequest` ; refuser (403/422) la suppression ou la rétrogradation de soi-même et du dernier administrateur (test RED : permissions).
- **Statut** : non corrigé, hors périmètre de la migration front.

## 2026-11-11 — Admin Client : pays stocké en id (avant phase 0) et colonnes vides sur la fiche — ✅ RÉSOLU (2026-11-11)

> Résolution (migration Client) : `AdminController::client` et `ClientController::edit` fournissent des options dont la valeur est le **nom** du pays (`Options::fromNames`), comme le fait la colonne `clients.pays` ; `ClientController::show` charge `orders.country:id,nom` et la fiche affiche le pays et `trans_state` (au lieu des attributs inexistants `pays` / `payment`). Test : `tests/Feature/AdminClientCountryTest.php`. **Contrôle des données (2026-11-11)** : base locale `dimancheabamako` (32 clients) — **aucun `pays` numérique**, donc aucune migration de correction écrite. Les valeurs sont des noms de pays (dont beaucoup absents de la table `countries`, qui n'a que 5 lignes : normal, la boutique accepte tous les pays, seul le back-office est limité à `countries`). **Reste manuel (prod)** : la requête n'a pas été lancée sur la production (hors de portée d'ici) ; à exécuter une fois : `select id, pays from clients where pays regexp '^[0-9]+$'`. Si des lignes ressortent, il faudra une migration `update clients join countries on countries.id = clients.pays set clients.pays = countries.nom` (non écrite faute d'anomalie locale).

- **Où** : `app/Http/Controllers/AdminController.php::client` (options `value => $row->id`, puis `Options::from` sans valeur personnalisée en phase 0), `resources/js/Pages/Admin/Client/Show.vue` (anciennement `row.pays`, `row.payment`).

## 2026-11-11 — PayLinkController : échec N-Genius invisible dans le back-office (non corrigé)

- **Où** : `app/Http/Controllers/PayLinkController.php` — `store`, `regenerate` (blocs `catch`), `update` (régénération silencieuse si le jeton ou la réponse manque).
- **Constat** : en cas d'échec, le contrôleur logge puis fait `flash()->error(...)` + `back()`. Les requêtes Inertia (XHR) ne rendent pas la vue HTML qui affiche les messages PHPFlasher : le message reste en session et apparaît au prochain chargement complet de page, sans rapport avec l'action. Côté Vue, `onSuccess` se déclenche quand même. La page migrée contourne le problème en comparant les données rechargées (total de lignes, `lien` modifié) avant d'annoncer un succès.
- **Piste** : renvoyer l'échec via `back()->withErrors([...])` (donc `onError`) ou une prop partagée `flash` ; même contrôle pour `update` (changement de montant sans nouveau lien = lien périmé avec l'ancien montant).
- **Statut** : non corrigé, hors périmètre front.

## 2026-11-11 — Slide / Image : ancien fichier supprimé avant l'enregistrement du nouveau, routes sans méthode (non corrigé)

- **Où** : `app/Http/Controllers/SlideController.php::update`, `ImageController::update` (`deleteStored()` avant `$uploader->store()`) ; `routes/web.php:50` (`Route::resource('slide', …)->except('index', 'create')`).
- **Constat** :
  1. L'ancien fichier est supprimé du disque avant que le nouveau soit stocké et que la ligne soit mise à jour : si `store()` échoue (disque plein, image illisible), la base pointe vers un fichier effacé et le slide / l'image produit s'affiche cassé.
  2. La resource `slide` expose `store` et `show` alors que `SlideController` n'a pas ces méthodes : `POST /admin/slide` et `GET /admin/slide/{slide}` renvoient une 500 au lieu d'un 404/405 (aucune page n'y mène).
- **Piste** : stocker le nouveau fichier, mettre à jour la ligne, puis supprimer l'ancien (après commit) ; `->only('edit', 'update', 'destroy')` pour `slide`.
- **Statut** : non corrigé, hors périmètre front.

## 2026-11-11 — Transport/Show morte et `TransportController::show` sans route

- **Où** : `resources/js/Pages/Admin/Transport/Show.vue` (page vide), `app/Http/Controllers/TransportController.php::show`.
- **Constat** : `transport.show` a été remplacée par `transport.zone` (`GET admin/transport/{transport}` → `get_trans_zone`, vérifié par `route:list`). `show()` n'est plus routée et rend une page vide, jamais atteinte. Non migrée.
- **Piste** : supprimer `Transport/Show.vue` et `TransportController::show`.
- **Statut** : non corrigé, hors périmètre.

## 2026-11-11 — `Promotion/Show` : catégorie du produit jamais affichée

- **Où** : `app/Http/Controllers/PromotionController.php::show` (`$promotion->load('products')`).
- **Constat** : l'ancienne page affichait `row.categorie?.nom`, mais la relation `categorie` n'est pas chargée : la ligne était toujours vide. Dans la version migrée la catégorie est omise ; l'ancienne image était un placeholder statique (`/admin/assets/imgs/items/1.jpg`), elle aussi retirée.
- **Piste** : `load('products.categorie')` puis réafficher la catégorie (et éventuellement `cover`) dans `Show.vue`.
- **Statut** : non corrigé, hors périmètre.

## 2026-11-11 — Création/édition de promotion, zone et transporteur impossible en local (`MassAssignmentException`)  — ✅ RÉSOLU (2026-11-11)

- **Où** : `PromotionController::store/update` (`Promotion::create($request->validated())`, champ `product_id`), `ZoneController::store/update` (`pays`), `TransportController::store/update` (`zone_id`). `app/Providers/AppServiceProvider.php:56` (`Model::shouldBeStrict(! app()->isProduction())`).
- **Constat** : `validated()` contient des champs de relation absents de `$fillable`. En mode strict (hors production) Eloquent lève `Add fillable property [product_id] to allow mass assignment on [App\Models\Promotion]` → 500 ; en production le champ est ignoré silencieusement, donc le bug est masqué. Reproduit en local par POST `admin/promotion` (422 de validation OK, puis 500).
- **Piste** : utiliser `$request->safe()->except('product_id')` (ou `only([...])`) avant `create`/`update`, + test Feature (création + `products` attachés).
- **Résolution** : `$request->safe()->except([champ de relation])` dans `store`/`update` des 3 contrôleurs. Pas d'ajout à `$fillable` : ce ne sont pas des colonnes. Les relations étaient déjà persistées explicitement (`attach`/`sync`/`saveMany`), donc en production seule la création du modèle ignorait ces clés (aucune donnée perdue). Tests : `tests/Feature/AdminRelationFieldsTest.php`.

## 2026-11-11 — Tarifs `shippings` existants avec une zone non desservie par leur transporteur

- **Où** : table `shippings` (local : ids 1, 2, 4, 7, 9), `database/factories/ShippingFactory.php` (`transport_id`/`zone_id` tirés au hasard par `rand(1, 2)`, sans tenir compte de la table pivot `transport_zone`).
- **Constat** : ces tarifs ne seront jamais retenus par `ShippingService::resolve` (filtre transport + zone). Depuis la règle ajoutée à `StoreShippingRequest`, leur édition exige de choisir une zone desservie par le transporteur ; la création/édition de nouveaux tarifs incohérents est refusée.
- **Piste** : revoir la factory (zone choisie parmi `transport->zones`) ; en production, lister les lignes incohérentes avec `Shipping::whereNotExists(... transport_zone ...)` et les corriger ou les supprimer à la main.
- **Statut** : non corrigé, hors périmètre.

## 2026-11-11 — Messages de validation « Le champ zone id est obligatoire »

- **Où** : `lang/fr/validation.php` (pas de traduction des attributs `zone_id`, `transport_id`, `poids_id`…), visible dans `Shipping/Index` et `Shipping/Update`.
- **Piste** : ajouter ces clés dans `attributes` (« zone », « transporteur », « tranche de poids »).
- **Statut** : non corrigé, cosmétique, hors périmètre.

## 2026-11-11 — Dashboard : `revenu` du mois et « ventes » comptées sur des bases différentes

- **Où** : `app/Http/Controllers/AdminController.php` (`dashboard`).
- **Constat** : `revenu` somme les lignes de **toutes** les commandes du mois (`whereMonth` sans filtre d'année ni d'état de paiement), tandis que « Ventes » ne compte que `trans_state = PURCHASED`, et le graphique compte **toutes** les commandes par jour. Les trois chiffres ne sont donc pas comparables (ex. local : 1 vente réglée, 3 commandes affichées, revenu incluant les non payées). `whereMonth` ignore aussi l'année (novembre 2025 + 2026 mélangés).
- **Statut** : non corrigé (migration front uniquement). Libellé du KPI renommé « Revenu du mois » côté Vue pour refléter la réalité actuelle.

## 2026-11-11 — Uploads impossibles en local : `getRealPath()` vide sur `C:\Windows\Temp` — ✅ RÉSOLU (2026-11-11, à valider après redémarrage d'Apache)

> Résolution : `upload_tmp_dir = "C:/laragon/tmp"` dans le `php.ini` du PHP web (`php-8.5.12`, sauvegarde `php.ini.bak-20261111`). Apache doit être redémarré (Laragon) ; à valider en créant un produit avec cover/vidéo. Aucun changement de code.

- **Où** : `app/Service/ImageUploadService.php::store` (`getRealPath()`), `ProductController::store`/`update` (`$request->video->storeAs(...)`), tout upload multipart en local (PHP web Laragon).
- **Constat** : le fichier uploadé est bien reçu (`isValid()` vrai, `file_exists` vrai, `C:\Windows\Temp\phpXXXX.tmp`) mais `getRealPath()` renvoie `false` et Intervention refuse de lire le dossier (« Directory "C:\Windows\Temp" is not readable »). Résultat : `POST /admin/product` en 500 (« Unable to decode from empty string ») dès qu'une cover est envoyée, indépendamment de l'UI.
- **Piste** : définir `upload_tmp_dir` (ex. `C:\laragon\tmp`) dans le `php.ini` du PHP web, ou décoder depuis `$file->get()` au lieu du chemin. Test de la migration Product faite avec un contournement temporaire (retiré, non commité).
- **Statut** : non corrigé, hors périmètre (environnement local).

## 2026-11-11 — `public/storage` n'est pas un lien vers `storage/app/public` en local — ✅ RÉSOLU (2026-11-11)

> Résolution : dossier réel supprimé (ses 4 fichiers existaient déjà dans `storage/app/public`), puis `php artisan storage:link` (jonction Windows). Images uploadées servies en 200.

- **Où** : `public/storage` (dossier réel contenant seulement `product/cover`, `LinkType` vide).
- **Constat** : les fichiers récents (images, vidéos, covers) sont écrits dans `storage/app/public` mais renvoient 404 via `/storage/...` ; les fiches produit affichent des images cassées.
- **Piste** : supprimer ce dossier puis `php artisan storage:link` (vérifier d'abord que son contenu existe aussi dans `storage/app/public`).
- **Statut** : non corrigé, hors périmètre.

## 2026-11-11 — Product/Show : colonne « Pays » toujours vide

- **Où** : `ProductController::show` charge `orders` sans `orders.country` ; l'ancienne page lisait `row.pays` (attribut inexistant sur `Order`).
- **Constat** : colonne supprimée dans la nouvelle page. Ancien bug visible : « stock » affichait `product.taille`, corrigé à la migration.
- **Piste** : `$product->load('images', 'orders.country')` puis réafficher `row.country?.nom`.
- **Statut** : non corrigé côté serveur, hors périmètre.

## 2026-11-11 — Création produit : les erreurs `image.*` et validation d'`image` sur Update

- **Où** : `UpdateProductRequest` (`color`/`taille` `required` alors que `StoreProductRequest` les accepte `nullable`).
- **Constat** : un produit créé sans couleur ni taille ne peut plus être modifié sans les renseigner.
- **Statut** : non corrigé, à arbitrer (règles incohérentes entre Store et Update).
