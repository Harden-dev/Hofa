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
        Schema::create('members', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('slug')->unique();
            $table->string('name');
            $table->string('phone');
            $table->string('email');
            $table->string('gender');
            $table->string('marital_status');
            $table->string('professional_profile');
            $table->ulid('benevolent_type_id');
            $table->boolean('is_benevolent')->default(false);
            $table->string('residence');
            $table->string('benevolent_experience')->nullable();

            $table->foreign('benevolent_type_id')->references('id')->on('benevole_types');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('members');
    }
};
