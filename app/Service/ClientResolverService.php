<?php

declare(strict_types=1);

namespace App\Service;

use App\Models\Client;

final class ClientResolverService
{
    /**
     * Resolve or create a client.
     *
     * L'e-mail est l'identité du client ; le téléphone seul ne prouve rien :
     * 1. e-mail connu : le client est mis à jour (identité confirmée) ;
     * 2. téléphone connu avec un autre e-mail : le client existant est renvoyé tel quel,
     *    sans modification (voir {@see self::isConfirmed()} pour rattacher un compte) ;
     * 3. sinon : création.
     *
     * @param  array{prenom: string, nom: string, contact: string, email: string, pays: string}  $data
     */
    public function resolve(array $data): Client
    {
        $byEmail = Client::where('email', $data['email'])->first();

        if ($byEmail) {
            $byEmail->update([
                'prenom' => $data['prenom'],
                'nom' => $data['nom'],
                'pays' => $data['pays'],
            ]);

            return $byEmail;
        }

        $byContact = Client::where('contact', $data['contact'])->first();

        if ($byContact) {
            return $byContact;
        }

        return Client::create([
            'prenom' => $data['prenom'],
            'nom' => $data['nom'],
            'contact' => $data['contact'],
            'email' => $data['email'],
            'pays' => $data['pays'],
        ]);
    }

    /**
     * Vrai si le client appartient bien à cet e-mail : seul cas où l'on peut lui rattacher un compte.
     */
    public function isConfirmed(Client $client, string $email): bool
    {
        return strcasecmp($client->email, $email) === 0;
    }
}
