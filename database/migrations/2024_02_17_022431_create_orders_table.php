<?php

use App\Enum\OrderEnum;
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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('country_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('transport_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('reference')->nullable()->unique();
            $table->string('trans_ref')->nullable()->unique();
            $table->longText('token')->nullable();
            $table->string('adresse');
            $table->string('postal')->nullable();
            $table->string('ville');
            $table->string('poids');
            $table->string('shipping');
            // $table->string('payment')->nullable();
            $table->string('commentaire')->nullable();
            $table->string('etat')->default(OrderEnum::SAVE);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
