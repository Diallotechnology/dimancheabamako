<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

final class CheckEmailVerified
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            // Si l'utilisateur est connecté mais n'a pas vérifié son email
            if (! Auth::user()->hasVerifiedEmail()) {
                return redirect()->route('verification.notice');
            }
        }

        return $next($request);
    }
}
