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
        Schema::table('soumissions', function (Blueprint $table) {
            $table->timestamp('dateSoumission')->nullable()->after('soumis');
            $table->string('document')->nullable()->after('dateSoumission');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('soumissions', function (Blueprint $table) {
            $table->dropColumn('dateSoumission');
            $table->dropColumn('document');
        });
    }
};
