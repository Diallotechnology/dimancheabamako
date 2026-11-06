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
     * - Inscription déjà en attente pour cet e-mail : le mot de passe et les données NE sont PAS remplacés
     *   (sinon un tiers imposerait son mot de passe au propriétaire de l'e-mail) ; un nouveau lien est envoyé à cet e-mail.
     * - Téléphone déjà tenu par l'inscription en attente d'un autre e-mail : rien n'est créé.
     *
     * @param  array{prenom: string, nom: string, pays: string, contact: string, email: string}  $data  contact au format E.164
     */
    public function request(array $data, string $plainPassword, string $locale): bool
    {
        $email = Str::lower($data['email']);

        $pending = PendingRegistration::where('email', $email)->first();

        // Jeton en clair : uniquement dans l'e-mail. La base ne garde que son hash.
        $token = Str::random(64);

        if ($pending === null) {
            if (PendingRegistration::where('contact', $data['contact'])->exists()) {
                return false;
            }

            PendingRegistration::create([
                'prenom' => $data['prenom'],
                'nom' => $data['nom'],
                'pays' => $data['pays'],
                'contact' => $data['contact'],
                'email' => $email,
                'role' => RoleEnum::CUSTOMER->value,
                'password' => Hash::make($plainPassword),
                'token' => PendingRegistration::hashToken($token),
                'expires_at' => now()->addHour(),
            ]);
        } else {
            // Le clair n'étant plus stocké, on ne peut pas renvoyer l'ancien lien : un nouveau jeton part
            // vers l'e-mail du propriétaire (l'ancien devient invalide). Mot de passe et données inchangés.
            $pending->update([
                'token' => PendingRegistration::hashToken($token),
                'expires_at' => now()->addHour(),
            ]);
        }

        // Après le commit de la commande : une commande annulée n'envoie aucun lien.
        DB::afterCommit(fn () => Mail::to($email)->send(new ConfirmRegistrationMail($token, $locale)));

        return true;
    }
}
