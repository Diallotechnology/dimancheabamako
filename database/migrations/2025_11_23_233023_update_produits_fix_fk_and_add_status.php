<?php

use App\Enum\ProductStatus;
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
        Schema::table('products', function (Blueprint $table) {
            $table->boolean('status')->default(false)->after('prix');
        });

        // 2️⃣ Correction de la contrainte de categories
        Schema::table('products', function (Blueprint $table) {
            // drop constraint avec nom généré automatiquement par Laravel
            $table->dropForeign(['categorie_id']);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('categorie_id')->nullable()->change();
        });

        Schema::table('products', function (Blueprint $table) {
            $table->foreign('categorie_id')
                ->references('id')->on('categories')
                ->cascadeOnUpdate()
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        // 1️⃣ Supprimer les colonnes ajoutées
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['status']);
        });

        // 2️⃣ Restaurer la FK initiale
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['categorie_id']);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('categorie_id')->nullable(false)->change();
        });

        Schema::table('products', function (Blueprint $table) {
            $table->foreign('categorie_id')
                ->references('id')->on('categories')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
        });
    }
};
