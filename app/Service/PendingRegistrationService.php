<?php

declare(strict_types=1);

namespace App\Service;

use App\Enum\RoleEnum;
use App\Mail\ConfirmRegistrationMail;
use App\Models\PendingRegistration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

/**
 * Source unique de l'inscription différée : un compte n'existe qu'après clic sur le lien envoyé à l'e-mail
 * (preuve de possession). Utilisé par le checkout ; le formulaire d'inscription pourra s'y brancher.
 */
final class PendingRegistrationService
{
    /**
     * Ouvre (ou relance) une inscription en attente et envoie le lien de confirmation après le commit.
     *
     * - Inscription déjà en attente pour cet e-mail : mot de passe et jeton NE sont PAS remplacés
     *   (sinon un tiers imposerait son mot de passe au propriétaire de l'e-mail) ; le lien existant est renvoyé.
     * - Téléphone déjà tenu par l'inscription en attente d'un autre e-mail : rien n'est créé.
     *
     * @param  array{prenom: string, nom: string, pays: string, contact: string, email: string}  $data  contact au format E.164
     */
    public function request(array $data, string $plainPassword, string $locale): bool
    {
        $email = Str::lower($data['email']);

        $pending = PendingRegistration::where('email', $email)->first();

        if ($pending === null) {
            if (PendingRegistration::where('contact', $data['contact'])->exists()) {
                return false;
            }

            $pending = PendingRegistration::create([
                'prenom' => $data['prenom'],
                'nom' => $data['nom'],
                'pays' => $data['pays'],
                'contact' => $data['contact'],
                'email' => $email,
                'role' => RoleEnum::CUSTOMER->value,
                'password' => Hash::make($plainPassword),
                'token' => Str::random(64),
                'expires_at' => now()->addHour(),
            ]);
        } else {
            $pending->update(['expires_at' => now()->addHour()]);
        }

        $token = $pending->token;

        // Après le commit de la commande : une commande annulée n'envoie aucun lien.
        DB::afterCommit(fn () => Mail::to($email)->send(new ConfirmRegistrationMail($token, $locale)));

        return true;
    }
}
