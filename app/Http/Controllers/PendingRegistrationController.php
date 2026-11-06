<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enum\RoleEnum;
use App\Jobs\RegisterMailJob;
use App\Models\PendingRegistration;
use App\Models\User;
use App\Service\CartService;
use App\Service\ClientResolverService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

final class PendingRegistrationController extends Controller
{
    public function __invoke(string $token)
    {
        $pending = PendingRegistration::where('token', $token)
            ->where('expires_at', '>', now())
            ->firstOrFail();

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
                $user = User::create([
                    'name' => "{$pending->prenom} {$pending->nom}",
                    'email' => $pending->email,
                    'password' => Hash::make($pending->password),
                    'role' => RoleEnum::CUSTOMER->value,
                    'client_id' => $client->id,
                ]);
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
}
