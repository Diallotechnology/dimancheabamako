<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('products', 'batch_id')) {
            return;
        }

        // Id du batch d'import IA qui a créé le produit (null pour les produits créés à la main).
        Schema::table('products', function (Blueprint $table) {
            $table->string('batch_id', 36)->nullable()->after('status')->index();
        });
    }

    public function down(): void
    {
        if (Schema::hasColumn('products', 'batch_id')) {
            Schema::table('products', function (Blueprint $table) {
                $table->dropIndex(['batch_id']);
                $table->dropColumn('batch_id');
            });
        }
    }
};
