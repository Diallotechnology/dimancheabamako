<?php

declare(strict_types=1);

namespace App\Service;

use App\Models\Client;

final class ClientResolverService
{
    /**
     * Resolve or create a client using contact/email as unique identifiers.
     *
     * Priority:
     * 1. contact
     * 2. email
     */
    public function resolve(array $data): Client
    {
        $client = Client::where('contact', $data['contact'])->first()
            ?? Client::where('email', $data['email'])->first();

        if ($client) {
            $client->update([
                'prenom' => $data['prenom'],
                'nom' => $data['nom'],
                'pays' => $data['pays'],
            ]);

            return $client;
        }

        return Client::create([
            'prenom' => $data['prenom'],
            'nom' => $data['nom'],
            'contact' => $data['contact'],
            'email' => $data['email'],
            'pays' => $data['pays'],
        ]);
    }
}
