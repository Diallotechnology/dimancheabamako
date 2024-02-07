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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('categorie_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('reference')->unique();
            $table->string('nom');
            $table->integer('prix');
            $table->string('poids');
            $table->integer('stock')->default(1);
            $table->string('color');
            $table->string('taille');
            $table->longText('description');
            $table->string('video')->nullable();
            $table->string('cover');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
