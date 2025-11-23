<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            // ⚠️ Avant d'ajouter l'unicité, vérifier qu'elle n'existe pas
            if (!Schema::hasColumn('clients', 'contact')) {
                throw new \Exception("La colonne 'contact' n'existe pas dans la table clients.");
            }

            Schema::table('clients', function (Blueprint $table) {
                // Ajoute un index unique si pas déjà présent
                $table->unique('contact', 'clients_contact_unique');
            });
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropUnique('clients_contact_unique');
        });
    }
};
