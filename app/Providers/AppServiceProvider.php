<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\User;
use App\Models\Product;
use App\Helper\ProductView;
use App\Service\PriceService;
use App\Service\CategoryService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Model;
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

        Gate::define('viewPulse', function (User $user) {
            return $user->isAdmin();
        });

        LogViewer::auth(function () {
            return Auth::user()->check() && Auth::user()->isAdmin();
        });
        // Stocke en cache et en session une seule fois
        $taux = Cache::rememberForever('taux_eur', function () {
            return DB::table('devises')->where('type', 'EUR')->value('taux');
        });

        session(['taux_eur' => $taux]);

        // Optionnel : pour Blade uniquement
        View::share('taux_eur', $taux);

        View::share('categories', app(CategoryService::class)->all());

        Collection::macro('forView', function () {
            $pricing = app(PriceService::class);

            return $this->map(fn(Product $model) => new ProductView($model, $pricing));
        });
    }
}
