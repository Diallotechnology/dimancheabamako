# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Stack réelle (diffère des conventions globales)

Ce dépôt est un site e-commerce (« Dimanche à Bamako ») en **Laravel 12 + PHP ^8.2**, avec **deux couches front distinctes** :

- **Vitrine publique** : Blade + **Livewire 3** (`app/Livewire`, `resources/views`). Routes `shop`, `panier`, etc.
- **Back-office admin** : **Inertia v1 + Vue 3** (JS, pas TypeScript) dans `resources/js/Pages/Admin/*`, routes préfixées `admin/`.

Écarts avec le `~/.claude/CLAUDE.md` global : pas de Wayfinder (les routes Vue passent par **Ziggy**, `route('nom')`), pas de Pest/Larastan installés (PHPUnit 11 uniquement, dossier `tests/` vide), pas de TypeScript, Tailwind non configuré (pas de `tailwind.config`, PostCSS sans plugin ; `resources/css/app.css` importe un CSS maison `assets/css/main.css`, style Bootstrap-like). Ne pas migrer ces éléments sans demande explicite.

## Commandes

```bash
composer install && npm install
php artisan migrate
npm run dev            # Vite (resources/js/app.js, SSR : resources/js/ssr.js)
npm run build
vendor/bin/pint        # style (preset laravel + règles strictes dans pint.json : final_class, declare_strict_types, strict_comparison…)
php artisan test       # ou vendor/bin/phpunit ; un seul test : php artisan test --filter=NomDuTest
php artisan schedule:work   # nécessaire en local : les paiements/commandes dépendent du scheduler
```

DB MySQL (`.env.example` : base `laravel`), `QUEUE_CONNECTION=sync` par défaut. `phpunit.xml` a la config SQLite commentée : les tests tourneraient sur la base MySQL configurée, à vérifier avant d'écrire des tests de feature.

## Architecture

**Rôles & accès** (`app/Enum/RoleEnum.php`, `RoleMiddleware`) : `Administrateur`, `Secretaire`, `Client`. Dans `routes/web.php`, le groupe `admin` est scindé en deux blocs de middleware `role:` : l'admin gère clients/utilisateurs/transport/zones/pays/devises/slides/paylinks ; le secrétaire gère commandes/produits/catégories/dashboard. Les routes de liste (`index`) sont servies par `AdminController` (qui rend les pages Inertia), les resources CRUD excluent `index`.

**Flux de commande** : `OrderController@store` → `Service/OrderService::createOrder` (transaction DB : pays, résolution du client via `ClientResolverService`, création éventuelle du compte `User`, panier via `CartService`, frais via `Shipping`/`Zone`/`Poids`/`Transport`). États d'une commande dans `OrderEnum` et colonne `trans_state` (ex. `PURCHASED`).

**Paiement** : passerelle **N-Genius** (`config/services.php` → `ngenius`, trait `Helper/OrderAPI`). Le statut est réconcilié par des commandes planifiées dans `app/Console/Kernel.php` (`app:check-payment` chaque minute, `app:check-pay-by-link`, purge des paiements/paylinks expirés) ; le scheduler lance aussi `queue:work --stop-when-empty`. Les mails (`app/Mail`, `app/Jobs`) passent par la queue.

**Devises** : deux devises, `CFA` (base) et `EUR`. Choix stocké en session (`devise`), taux en session/cache (`taux_eur`, invalidé par `Models/Devise`). Le formatage de prix est centralisé dans `Service/PriceService` (à réutiliser ; ne pas dupliquer la conversion dans les vues/composants). Promotions via `Models/Promotion` (expiration quotidienne au scheduler).

**Inscription différée** : `PendingRegistration` + `PendingRegistrationController` (token par e-mail, purge horaire des expirés) + `NotDisposableEmail` rule.

**i18n** : `fr`/`en` (`lang/`, middleware `LangManager`, route `lang/{lang}`, locale en session).

**Front admin** : `resources/js/app.js` enregistre globalement `Select`, `Select2`, `VueSelect`, `Input`, `TextArea`, `InputDate` et Ziggy ; les pages sont résolues par glob `Pages/**/*.vue`. Composants partagés dans `Components/`, filtre/pagination via `Composables/useFilter.js`.

## Pièges

- `routes/web.php` contient une route de debug `test` (corps commenté) — ne pas s'en servir ni l'étendre.
- `_ide_helper.php` / `_ide_helper_models.php` sont générés (barryvdh/laravel-ide-helper) ; ne pas les éditer à la main.
- `OrderService::createOrder` contient une résolution de client redondante avant l'appel à `ClientResolverService` ; la source de vérité est le service.
