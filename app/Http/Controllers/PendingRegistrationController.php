<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enum\RoleEnum;
use App\Jobs\RegisterMailJob;
use App\Models\PendingRegistration;
use App\Models\User;
use App\Service\CartService;
use App\Service\ClientResolverService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

final class PendingRegistrationController extends Controller
{
    public function show(string $token): View
    {
        $this->findValid($token);

        return view('auth.confirm-registration', ['token' => $token]);
    }

    public function store(string $token): RedirectResponse
    {
        $pending = $this->findValid($token);

        $confirmed = DB::transaction(function () use ($pending): bool {
            $resolver = app(ClientResolverService::class);
            $client = $resolver->resolve([
                'prenom' => $pending->prenom,
                'nom' => $pending->nom,
                'contact' => $pending->contact,
                'email' => $pending->email,
                'pays' => $pending->pays,
            ]);

            // Le téléphone appartient déjà à un Client d'un autre e-mail : aucun compte, aucune connexion.
            if (! $resolver->isConfirmed($client, $pending->email)) {
                $pending->delete();

                return false;
            }

            $user = User::where('email', $pending->email)->first();

            if (! $user) {
                // Le clic sur le lien prouve la possession de l'e-mail ; le mot de passe vient d'être choisi
                // par l'utilisateur : pas de second changement forcé au premier login.
                $user = User::create([
                    'name' => "{$pending->prenom} {$pending->nom}",
                    'email' => $pending->email,
                    'change_password' => true,
                    // Déjà haché par PendingRegistrationService : le cast `hashed` de User le conserve tel quel.
                    'password' => $pending->password,
                    'role' => RoleEnum::CUSTOMER->value,
                    'client_id' => $client->id,
                ]);
                $user->forceFill(['email_verified_at' => now()])->save();
            } elseif ($user->client_id === null) {
                $user->update(['client_id' => $client->id]);
            }

            // envoie éventuels listeners
            // event(new Registered($user));
            // ton mail de bienvenue si souhaité
            RegisterMailJob::dispatch($user);
            $oldSessionId = session()->getId(); // ⚠️ Crutial
            // connexion automatique
            Auth::login($user);

            app(CartService::class)->mergeGuestCartToUser(Auth::id(), $oldSessionId);

            $pending->delete();

            return true;
        });

        if (! $confirmed) {
            return to_route('register')->withErrors(['contact' => __('messages.register_phone_conflict')]);
        }

        return to_route('register-email', 1)->with('status', __('messages.register_success_msg'));
    }

    private function findValid(string $token): PendingRegistration
    {
        return PendingRegistration::where('token', PendingRegistration::hashToken($token))
            ->where('expires_at', '>', now())
            ->firstOrFail();
    }
}
