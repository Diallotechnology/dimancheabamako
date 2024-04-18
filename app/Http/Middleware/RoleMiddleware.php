<?php

namespace App\Http\Middleware;

use App\Enum\RoleEnum;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        // Obtenez l'objet utilisateur à partir de la requête (assurez-vous d'avoir la fonctionnalité d'authentification configurée)
        $user = $request->user();

        // Vérifiez si l'utilisateur a le rôle requis
        if ($user && $this->hasRole($user, $role)) {
            return $next($request); // L'utilisateur a le rôle approprié, laissez-le accéder à la route suivante
        }

        // L'utilisateur n'a pas le rôle approprié, vous pouvez personnaliser la réponse d'erreur selon vos besoins
        return abort(403);
    }

    /**
     * Check if the user has the specified role.
     *
     * @param  \App\Model\User  $user
     */
    private function hasRole(User $user, string $role): bool
    {
        // Utilisez les fonctions de la classe User pour vérifier le rôle de l'utilisateur
        return match ($role) {
            RoleEnum::ADMIN->value => $user->isAdmin(),
            RoleEnum::SECRTETAIRE->value => $user->isSecretaire() || $user->isAdmin(),
            RoleEnum::CUSTOMER->value => $user->isClient() || $user->isAdmin(),
            default => false, // Rôle non pris en charge
        };
    }
}
