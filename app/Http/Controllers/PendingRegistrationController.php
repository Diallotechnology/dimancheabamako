<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Client;
use App\Service\CartService;
use App\Jobs\RegisterMailJob;
use Illuminate\Support\Facades\DB;
use App\Models\PendingRegistration;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\Events\Registered;

class PendingRegistrationController extends Controller
{
    public function __invoke(string $token)
    {
        $pending = PendingRegistration::where('token', $token)
            ->where('expires_at', '>', now())
            ->firstOrFail();

        DB::transaction(function () use ($pending) {

            $user = User::create([
                'name' => $pending->prenom . ' ' . $pending->nom,
                'email' => $pending->email,
                'role' => $pending->role,
                'password' => $pending->password,
                'change_password' => true,
            ]);

            Client::create([
                'prenom' => $pending->prenom,
                'nom' => $pending->nom,
                'contact' => $pending->contact,
                'email' => $pending->email,
                'pays' => $pending->pays,
                'user_id' => $user->id,

            ]);

            // envoie éventuels listeners
            // event(new Registered($user));
            // ton mail de bienvenue si souhaité
            RegisterMailJob::dispatch($user);
            // connexion automatique
            Auth::login($user);

            app(CartService::class)->mergeGuestCartToUser($user->id);

            $pending->delete();
        });

        flash()->success('Votre compte a été activé avec succès !');

        return redirect('/')->with('status', 'Votre compte a été activé avec succès !');
    }
}
