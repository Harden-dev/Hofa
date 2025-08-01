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
        Schema::table('sessions', function (Blueprint $table) {
            // Modifier la colonne user_id pour accepter des ULIDs (26 caractères)
            $table->string('user_id', 26)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sessions', function (Blueprint $table) {
            // Revenir à la taille originale (probablement 255 ou moins)
            $table->string('user_id')->nullable()->change();
        });
    }
};
