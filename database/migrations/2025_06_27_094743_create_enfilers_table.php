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
        Schema::create('enfilers', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('slug')->unique();
            $table->ulid('enfiler_type_id');
            $table->string('name');
            $table->string('phone');
            $table->string('email');
            $table->string('motivation')->nullable();
            $table->boolean('is_active')->default(true);

            $table->foreign('enfiler_type_id')->references('id')->on('enfiler_types');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('enfilers');
    }
};
