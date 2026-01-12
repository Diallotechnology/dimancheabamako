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

        DB::transaction(function () use ($pending) {

            $client = app(ClientResolverService::class)->resolve([
                'prenom' => $pending->prenom,
                'nom' => $pending->nom,
                'contact' => $pending->contact,
                'email' => $pending->email,
                'pays' => $pending->pays,
            ]);

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
        });

        return to_route('register-email', 1)->with('status', __('messages.register_success_msg'));
    }
}
