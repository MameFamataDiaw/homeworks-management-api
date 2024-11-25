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
        Schema::create('soumissions', function (Blueprint $table) {
            $table->id();
            $table->date('dateAssigned');
            $table->date('dateSoumission');
            $table->enum('statut',['en cours','soumis','non rendu'])->default('en cours');
            $table->decimal('note')->nullable();
            $table->string('commentaire')->nullable();
            $table->foreignId('devoir_id');
            $table->foreignId('eleve_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('soumissions');
    }
};
