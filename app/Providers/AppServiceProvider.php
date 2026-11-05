<?php

declare(strict_types=1);

namespace App\Providers;

use App\Helper\ProductView;
use App\Models\Product;
use App\Service\CategoryService;
use App\Service\PriceService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Opcodes\LogViewer\Facades\LogViewer;

final class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void {}

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Model::automaticallyEagerLoadRelationships();
        Model::shouldBeStrict(! app()->isProduction());
        URL::forceHttps(app()->isProduction());

        // Dev derrière un tunnel HTTPS (cloudflared/ngrok) : N-Genius refuse les URLs de retour non publiques,
        // donc route()/asset() doivent partir de APP_URL et non de l'hôte reçu.
        if (app()->isLocal()) {
            URL::forceRootUrl(config('app.url'));
            URL::forceScheme(parse_url((string) config('app.url'), PHP_URL_SCHEME) ?: 'http');
        }

        LogViewer::auth(function () {
            return Auth::user()->check() && Auth::user()->isAdmin();
        });
        // Stocke en cache et en session une seule fois.
        // Garde : la table peut ne pas exister (premier `migrate`, base de test vide).
        $taux = Cache::has('taux_eur')
            ? Cache::get('taux_eur')
            : (Schema::hasTable('devises')
                ? Cache::rememberForever('taux_eur', fn () => DB::table('devises')->where('type', 'EUR')->value('taux'))
                : null);

        session(['taux_eur' => $taux]);

        // Optionnel : pour Blade uniquement
        View::share('taux_eur', $taux);

        // Évalué au rendu de la vue (pas au boot) : aucune requête tant qu'aucune vue n'est rendue.
        View::composer('*', fn ($view) => $view->with('categories', app(CategoryService::class)->all()));

        Collection::macro('forView', function () {
            $pricing = app(PriceService::class);

            return $this->map(fn (Product $model) => new ProductView($model, $pricing));
        });
    }
}
