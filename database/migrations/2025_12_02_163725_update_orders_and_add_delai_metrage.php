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
        // 1️⃣ Ajout des colonnes (pas de if/hasColumn)
        Schema::table('orders', function (Blueprint $table) {
            $table->integer('delai')->nullable()->default(0)->after('etat');
            $table->integer('metrage')->nullable()->default(0)->after('delai');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 1️⃣ Supprimer les colonnes ajoutées
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['delai', 'metrage']);
        });
    }
};
