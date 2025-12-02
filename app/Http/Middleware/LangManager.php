<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

final class LangManager
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Vérifiez si la langue est définie dans la requête
        $locale = Session::get('locale') ?? 'fr';
        Session::put('locale', $locale);
        App::setLocale($locale);

        return $next($request);
    }
}
