<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Les jetons d'inscription en attente ne sont plus stockés en clair : on hache ceux déjà en base
 * pour que les liens déjà envoyés par e-mail restent valables (le contrôleur hache le jeton reçu).
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('pending_registrations')->orderBy('id')->each(function (object $row): void {
            DB::table('pending_registrations')->where('id', $row->id)->update(['token' => hash('sha256', (string) $row->token)]);
        });
    }

    public function down(): void
    {
        // Irréversible : le jeton en clair n'est pas récupérable.
    }
};
