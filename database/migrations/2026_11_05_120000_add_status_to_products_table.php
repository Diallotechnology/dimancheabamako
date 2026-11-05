<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('products', 'status')) {
            return;
        }

        // Défaut « published » : les produits existants restent visibles en boutique.
        Schema::table('products', function (Blueprint $table) {
            $table->string('status', 20)->default('published')->after('stock')->index();
        });
    }

    public function down(): void
    {
        if (Schema::hasColumn('products', 'status')) {
            Schema::table('products', function (Blueprint $table) {
                $table->dropIndex(['status']);
                $table->dropColumn('status');
            });
        }
    }
};
