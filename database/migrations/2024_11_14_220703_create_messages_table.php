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
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->string('contenu');
            $table->enum('type',['question','rappel','reponse'])->default('question')->nullable();
            $table->enum('statut',['lu','non lu','repondu'])->default('non lu');
            $table->foreignId('parentt_id');
            $table->foreignId('enseignant_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
