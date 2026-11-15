<?php

declare(strict_types=1);

use Illuminate\Support\Facades\File;

/*
 * Règles de la vitrine refondue (Blade + Livewire + Tailwind), migrée page par page hors du thème Evara.
 * Les listes LEGACY_* recensent l'existant non conforme : elles ne doivent que rétrécir.
 * Un fichier listé qui devient conforme fait échouer le test tant qu'il n'est pas retiré de la liste.
 */

/**
 * Vues déjà migrées sur le layout `shop` : aucune trace de Bootstrap / jQuery / Evara autorisée.
 * À compléter à chaque page migrée (phase 1 : index, livewire/…).
 *
 * @return list<string> chemins relatifs à resources/views
 */
function migratedStorefrontViews(): array
{
    $shopComponents = collect(File::allFiles(resource_path('views/components/shop')))
        ->map(fn (SplFileInfo $file): string => 'components/shop/'.str_replace('\\', '/', $file->getRelativePathname()));

    return $shopComponents
        ->push(
            'layouts/shop.blade.php',
            'livewire/counter.blade.php',
            'livewire/quick-add-modal.blade.php',
            'index.blade.php',
            'category.blade.php',
            'product-show.blade.php',
            'livewire/panier.blade.php',
            'about.blade.php',
            'livraison.blade.php',
            'contact.blade.php',
            'profil.blade.php',
            'validate.blade.php',
            'components/auth-session-status.blade.php',
            'livewire/product-list.blade.php',
            ...collect(File::files(resource_path('views/auth')))
                ->map(fn (SplFileInfo $file): string => 'auth/'.$file->getFilename())
                ->all(),
        )
        ->values()
        ->all();
}

/**
 * @param  list<string>  $patterns  regex
 * @return array<string, list<string>> fichier => motifs trouvés
 */
function scanViews(iterable $relativePaths, array $patterns): array
{
    $hits = [];

    foreach ($relativePaths as $relative) {
        // Les commentaires Blade documentent les règles (ex. « sans Bootstrap ») : on ne les analyse pas.
        $contents = (string) preg_replace('/\{\{--.*?--\}\}/s', '', File::get(resource_path('views/'.$relative)));

        foreach ($patterns as $pattern) {
            if (preg_match_all($pattern, $contents, $matches)) {
                foreach ($matches[0] as $match) {
                    $hits[$relative][] = $match;
                }
            }
        }
    }

    return $hits;
}

/**
 * Vues de la vitrine (hors mails, facture, vendor, admin Inertia, sitemap XML).
 *
 * @return list<string>
 */
function storefrontViews(): array
{
    return collect(File::allFiles(resource_path('views')))
        ->map(fn (SplFileInfo $file): string => str_replace('\\', '/', $file->getRelativePathname()))
        ->filter(fn (string $path): bool => str_ends_with($path, '.blade.php'))
        ->reject(fn (string $path): bool => str_starts_with($path, 'vendor/')
            || str_starts_with($path, 'email/')
            || str_starts_with($path, 'sitemap/')
            || in_array($path, ['invoice.blade.php', 'admin.blade.php'], true))
        ->values()
        ->all();
}

test('migrated storefront views carry no Bootstrap, jQuery or Evara markup', function () {
    $hits = scanViews(migratedStorefrontViews(), [
        '/data-bs-[a-z-]+/',
        '/\bbootstrap\b/i',
        '/\bjquery\b/i',
        '/\$\(\s*["\'.#]/',
        '/assets\/css\/main\.css/',
        '/@include\(\s*[\'"]layouts\.nav/',
        '/<x-app-layout|layouts::app|<x-guest-layout/',
        // Classe isolée (délimitée par espace ou guillemet), pour ne pas confondre `row` et `sm:flex-row`.
        '/class="[^"]*(?<=["\s])(?:col-(?:xs|sm|md|lg|xl)-\d+|row|btn|btn-[a-z-]+|form-control|form-select|d-none|d-flex|wow|fi-rs-[a-z-]+|mini-cart-icon|pro-count)(?=["\s])[^"]*"/',
    ]);

    expect($hits)->toBe([], 'Balisage Evara/Bootstrap dans une vue migrée : '.json_encode($hits, JSON_UNESCAPED_SLASHES));
});

test('the retired Evara layouts and components stay deleted', function () {
    foreach (['layouts/app.blade.php', 'layouts/nav.blade.php', 'layouts/guest.blade.php', 'dashboard.blade.php'] as $view) {
        expect(resource_path('views/'.$view))->not->toBeFile();
    }
});

test('storefront views do not hardcode application links', function () {
    // Vues pas encore migrées qui contiennent des liens en dur (à corriger lors de leur migration).
    $legacy = [];

    // href/action vers un chemin absolu de l'application ; les assets statiques passent par asset().
    $pattern = '/\b(?:href|action)=(["\'])\/(?!\/)[^"\']*\1/';
    $hits = scanViews(storefrontViews(), [$pattern]);

    $unexpected = array_diff_key($hits, array_flip($legacy));
    $fixed = array_values(array_diff($legacy, array_keys($hits)));

    expect($unexpected)->toBe([], 'Lien(s) en dur : utiliser route(). '.json_encode($unexpected, JSON_UNESCAPED_SLASHES))
        ->and($fixed)->toBe([], 'Ces vues n\'ont plus de lien en dur : les retirer de $legacy.');
});

test('storefront price display goes through PriceService', function () {
    // Formatage de prix encore dupliqué : plus aucun fichier connu (Panier migré en phase 4).
    $legacy = [];

    $files = collect([...File::allFiles(app_path('Livewire')), ...File::allFiles(resource_path('views/livewire')), ...File::allFiles(resource_path('views/components/shop'))])
        ->map(fn (SplFileInfo $file): string => str_replace('\\', '/', str_replace(base_path().DIRECTORY_SEPARATOR, '', $file->getPathname())));

    $offenders = $files
        ->filter(fn (string $path): bool => str_contains(File::get(base_path($path)), 'number_format('))
        ->values()
        ->all();

    expect(array_values(array_diff($offenders, $legacy)))->toBe([], 'number_format() hors PriceService : utiliser PriceService::format() ou <x-shop.price>.')
        ->and(array_values(array_diff($legacy, $offenders)))->toBe([], 'Ces fichiers ne formatent plus de prix : les retirer de $legacy.');
});

test('Livewire views have a single HTML root element, not a Blade directive at the top', function () {
    // Un @if/@foreach au sommet fait perdre à Livewire son élément racine : le HTML est alors imbriqué à chaque
    // mise à jour et l'erreur « Snapshot missing on Livewire component » casse le composant côté navigateur.
    $offenders = collect(File::allFiles(resource_path('views/livewire')))
        ->filter(function (SplFileInfo $file): bool {
            $contents = (string) preg_replace('/\{\{--.*?--\}\}/s', '', File::get($file->getPathname()));

            return ! str_starts_with(mb_ltrim($contents), '<');
        })
        ->map(fn (SplFileInfo $file): string => $file->getFilename())
        ->values()
        ->all();

    expect($offenders)->toBe([], 'Vue(s) Livewire dont la racine n\'est pas un élément HTML : '.implode(', ', $offenders));
});

test('every static file referenced through asset() exists in public/, and Evara folders are gone', function () {
    $sources = collect([
        ...File::allFiles(resource_path('views')),
        ...File::allFiles(app_path()),
    ])->map(fn (SplFileInfo $file): string => $file->getPathname());

    $missing = [];
    foreach ($sources as $path) {
        preg_match_all('/asset\(\s*[\'"]([^\'"]+)[\'"]\s*\)/', File::get($path), $matches);
        foreach ($matches[1] as $relative) {
            if (! File::exists(public_path($relative))) {
                $missing[] = str_replace(base_path().DIRECTORY_SEPARATOR, '', $path).' → '.$relative;
            }
        }
    }

    expect($missing)->toBe([], 'Fichier(s) référencé(s) par asset() absent(s) de public/ : '.json_encode($missing, JSON_UNESCAPED_SLASHES))
        ->and(public_path('assets'))->not->toBeDirectory()
        ->and(public_path('admin'))->not->toBeDirectory()
        ->and(resource_path('css/app.css'))->not->toBeFile();
});
