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
        Schema::table('products', function (Blueprint $table) {
            // 1️⃣ Ajouter le champ status si non existant
            if (!Schema::hasColumn('products', 'status')) {
                $table->boolean('status')->default(true)->after('prix'); // ajuste la colonne selon ta structure
            }
        });

        // 2️⃣ Modifier la contrainte sur categorie_id
        // On doit supprimer la contrainte FK existante avant d'en recréer une nouvelle
        DB::statement('ALTER TABLE products DROP FOREIGN KEY products_categorie_id_foreign');

        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('categorie_id')
                ->nullable() // devient nullable sinon nullOnDelete ne fonctionnera pas
                ->change();
        });

        Schema::table('products', function (Blueprint $table) {
            $table->foreign('categorie_id')
                ->references('id')->on('categories')
                ->cascadeOnUpdate()
                ->nullOnDelete(); // 🔥 empêche la suppression du produit lors de delete catégorie
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // rollback sécurisé
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('status');
        });

        // restaurer la FK en cascade si rollback
        DB::statement('ALTER TABLE products DROP FOREIGN KEY products_categorie_id_foreign');
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
