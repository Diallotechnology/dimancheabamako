<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Enum\RoleEnum;
use App\Models\Client;
use App\Jobs\RegisterMailJob;
use Illuminate\Support\Facades\DB;
use App\Models\PendingRegistration;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cache;
use Illuminate\Auth\Events\Registered;
use App\Http\Requests\StorePendingRegistrationRequest;
use App\Http\Requests\UpdatePendingRegistrationRequest;


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

            CartService::make()->mergeGuestCartToUser($user->id);

            $pending->delete();
        });

        flash()->success('Votre compte a été activé avec succès !');
        return redirect('/')->with('status', 'Votre compte a été activé avec succès !');
    }
}
