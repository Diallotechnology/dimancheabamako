<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Jobs\RegisterMailJob;
use App\Models\Client;
use App\Models\PendingRegistration;
use App\Models\User;
use App\Service\CartService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

final class PendingRegistrationController extends Controller
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
            ]);

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

        $msg = __('messages.register_success_msg');
        flash()->success($msg);

        return redirect('profil')->with('status', $msg);
    }
}
