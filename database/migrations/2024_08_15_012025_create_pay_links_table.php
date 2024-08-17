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
        Schema::create('pay_links', function (Blueprint $table) {
            $table->id();
            $table->string('trans_ref')->nullable()->unique();
            $table->string('trans_state')->nullable();
            $table->string('name');
            $table->string('contact');
            $table->longText('lien');
            $table->integer('montant');
            $table->string('etat')->default('Pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pay_links');
    }
};
