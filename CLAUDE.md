# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Stack réelle (diffère des conventions globales)

Ce dépôt est un site e-commerce (« Dimanche à Bamako ») en **Laravel 13 + PHP ^8.4**, avec **deux couches front distinctes** :

- **Vitrine publique** : Blade + **Livewire 4** (`app/Livewire`, `resources/views`). Routes `shop`, `panier`, etc.
- **Back-office admin** : **Inertia 3 + Vue 3 + TypeScript (strict)** dans `resources/js/Pages/Admin/*`, routes préfixées `admin/`. UI **shadcn-vue** (reka-ui) sur **Tailwind 4** (plugin Vite `@tailwindcss/vite`, tokens et palette dans `resources/css/admin.css`, sans `tailwind.config`). La vitrine Livewire garde sa propre feuille (`public/assets/css/main.css`) ; ne pas mélanger les deux.

Les routes du front admin passent par **Wayfinder** : `@/actions/App/Http/Controllers/…` (une action par méthode de contrôleur, ex. `destroy(row.id)`) et `@/routes`. Ces dossiers (`resources/js/actions`, `routes`, `wayfinder`) sont **générés et ignorés par git** : `npm run build`/`dev` les régénèrent via le plugin Vite, ou `npm run wayfinder` (`php artisan wayfinder:generate`). Ziggy et `route('nom')` ont été retirés ; ne jamais coder une URL en dur.

Écarts avec le `~/.claude/CLAUDE.md` global : la stack front admin est maintenant alignée (Wayfinder, TypeScript, Tailwind 4) ; seule la **vitrine** reste en Blade + Livewire. Le thème Bootstrap « Evara » (`public/admin/assets`) n'est plus qu'un reste : il ne sert qu'à la facture client (`resources/views/invoice.blade.php`) et à l'image de repli produit (`Product.php`, `logo.svg`). Ne pas le réétendre ; tout nouvel écran admin passe par Vue + shadcn-vue. **Pest 4 + Larastan niveau 6** sont installés (`phpstan.neon`, tests dans `tests/Feature`).

## Commandes

```bash
composer install && npm install
php artisan migrate
npm run dev            # Vite (resources/js/app.ts, SSR : resources/js/ssr.js)
npm run build
npm run types:check    # vue-tsc --noEmit (tsconfig strict)
npm run wayfinder      # regénère resources/js/actions et routes (aussi fait par le plugin Vite)
vendor/bin/pint        # style (preset laravel + règles strictes dans pint.json : final_class, declare_strict_types, strict_comparison…)
vendor/bin/pest        # ou composer test ; un seul fichier : vendor/bin/pest tests/Feature/NomTest.php
vendor/bin/phpstan analyse   # Larastan niveau 6
php artisan schedule:work   # nécessaire en local : les paiements/commandes dépendent du scheduler
```

DB MySQL (`.env.example` : base `laravel`), `QUEUE_CONNECTION=sync` par défaut. `phpunit.xml` force SQLite en mémoire pour les tests (`RefreshDatabase` dans `tests/Pest.php`) : les tests ne touchent pas la base MySQL.

## Architecture

**Rôles & accès** (`app/Enum/RoleEnum.php`, `RoleMiddleware`) : `Administrateur`, `Secretaire`, `Client`. Dans `routes/web.php`, le groupe `admin` est scindé en deux blocs de middleware `role:` : l'admin gère clients/utilisateurs/transport/zones/pays/devises/slides/paylinks ; le secrétaire gère commandes/produits/catégories/dashboard. Les routes de liste (`index`) sont servies par `AdminController` (qui rend les pages Inertia), les resources CRUD excluent `index`.

**Flux de commande** : `OrderController@store` → `Service/OrderService::createOrder` (transaction DB : pays, résolution du client via `ClientResolverService`, création éventuelle du compte `User`, panier via `CartService`, frais via `Shipping`/`Zone`/`Poids`/`Transport`). États d'une commande dans `OrderEnum` et colonne `trans_state` (ex. `PURCHASED`).

**Paiement** : passerelle **N-Genius** (`config/services.php` → `ngenius`, trait `Helper/OrderAPI`). Le statut est réconcilié par des commandes planifiées dans `app/Console/Kernel.php` (`app:check-payment` chaque minute, `app:check-pay-by-link`, purge des paiements/paylinks expirés) ; le scheduler lance aussi `queue:work --stop-when-empty`. Les mails (`app/Mail`, `app/Jobs`) passent par la queue.

**Devises** : deux devises, `CFA` (base) et `EUR`. Choix stocké en session (`devise`), taux en session/cache (`taux_eur`, invalidé par `Models/Devise`). Le formatage de prix est centralisé dans `Service/PriceService` (à réutiliser ; ne pas dupliquer la conversion dans les vues/composants). Promotions via `Models/Promotion` (expiration quotidienne au scheduler).

**Inscription différée** : `PendingRegistration` + `PendingRegistrationController` (token par e-mail, purge horaire des expirés) + `NotDisposableEmail` rule.

**i18n** : `fr`/`en` (`lang/`, middleware `LangManager`, route `lang/{lang}`, locale en session).

**Front admin** : `resources/js/app.ts` crée l'app Inertia sans composant global ni plugin de routes ; les pages sont résolues par glob `Pages/**/*.vue` et enveloppées dans **`Layouts/AdminLayoutV2.vue`** (barre latérale pilotée par `Layouts/adminNavigation.ts`, `Toaster` sonner inclus ; props `title`, `description`, slot `#actions`). Deux familles de composants :
- `Components/ui/` : primitives shadcn-vue (button, input, table, dialog, alert-dialog, native-select, badge, sidebar, pagination…), à importer explicitement (`@/Components/ui/button`).
- `Components/Admin/` : briques métier réutilisables. `DataTable` (liste paginée ou tableau + `LaravelPagination`), `CrudDialog` (création/édition en modale), `ConfirmDelete` / `ConfirmDialog` (suppression confirmée, attend un 2xx, le contrôleur répond `{success}` via `DeleteAction::supp`), `FormField` (libellé, erreur, aide, ids ARIA), `Combobox` (liste avec recherche), `DateTimePicker`, `ImageUpload`, `MultiImageUpload`, `VideoUpload`, `ProductFormFields`, `OrdersChart`, `AdminSidebar`.
- Logique partagée : `Composables/useFilter.ts` (filtres synchronisés avec la query string, cible = action Wayfinder), `useTransportZones.ts`, `lib/format.ts` (`formatXof`), `lib/utils.ts` (`cn`). Types des modèles Inertia dans `resources/js/types/` (`models.ts`, `Paginated<T>`, `Option`).
Pattern d'une page de liste : `useFilter` + `DataTable` + `ConfirmDelete` (voir `Pages/Admin/Category/Index.vue`) ; formulaire : `useForm` + `form.submit(action())` + toast `vue-sonner`.

## Pièges

- `routes/web.php` contient une route de debug `test` (corps commenté) — ne pas s'en servir ni l'étendre.
- `_ide_helper.php` / `_ide_helper_models.php` sont générés (barryvdh/laravel-ide-helper) ; ne pas les éditer à la main.
- `OrderService::createOrder` contient une résolution de client redondante avant l'appel à `ClientResolverService` ; la source de vérité est le service.
