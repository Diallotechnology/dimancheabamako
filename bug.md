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
