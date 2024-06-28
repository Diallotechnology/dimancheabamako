<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class LangManager
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Vérifiez si la langue est définie dans la session
        if (Session::has('locale')) {
            App::setLocale(Session::get('locale'));
        } else {
            // Définir la langue par défaut si aucune langue n'est définie dans la session
            App::setLocale(config('app.locale'));
        }

        return $next($request);
    }
}
