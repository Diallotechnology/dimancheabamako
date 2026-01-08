<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\User;
use App\Enum\RoleEnum;
use App\Models\Client;
use App\Models\Country;
use App\Service\CartService;
use App\Jobs\RegisterMailJob;
use Illuminate\Support\Facades\DB;
use App\Models\PendingRegistration;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\Events\Registered;

final class PendingRegistrationController extends Controller
{
    public function __invoke(string $token)
    {
        $pending = PendingRegistration::where('token', $token)
            ->where('expires_at', '>', now())
            ->firstOrFail();

        DB::transaction(function () use ($pending) {

            $user = User::firstOrCreate(
                ['email' => $pending->email],
                [
                    'name' => $pending->prenom . ' ' . $pending->nom,
                    'email' => $pending->email,
                    'password' => Hash::make($pending->password),
                    'change_password' => true,
                    'role' => RoleEnum::CUSTOMER->value,
                ]
            );

            $country = Country::where('nom', $pending->pays)->first();

            // Chercher client par contact
            $clientByContact = Client::where('contact', $pending->contact)->first();

            // Chercher client par email
            $clientByEmail = Client::where('email', $pending->email)->first();

            if ($clientByContact) {
                // Contact existe → update uniquement les champs autres que contact
                $clientByContact->update([
                    'prenom' => $pending->prenom,
                    'nom'    => $pending->nom,
                    'pays'   => $country->nom,
                    // 'email' non touché pour éviter collision
                ]);
            } elseif ($clientByEmail) {
                // Email existe → update uniquement les champs autres que email
                $clientByEmail->update([
                    'prenom'  => $pending->prenom,
                    'nom'     => $pending->nom,
                    'pays'    => $country->nom,
                    // 'contact' non touché pour éviter collision
                ]);
            } else {
                // Ni contact ni email n’existe → créer un nouveau client
                Client::create([
                    'prenom'  => $pending->prenom,
                    'nom'     => $pending->nom,
                    'contact' => $pending->contact,
                    'email'   => $pending->email,
                    'pays'    => $pending->pays,
                ]);
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
