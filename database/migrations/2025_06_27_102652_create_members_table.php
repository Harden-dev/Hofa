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
            $table->enum('type', ['individual', 'company']);
            $table->string('name')->nullable();
            $table->string('bossName')->nullable();
            $table->string('phone');
            $table->string('email');
            $table->string('gender')->nullable();
            $table->string('nationality')->nullable();
            $table->string('matrimonial')->nullable();
          //  $table->ulid('benevolent_type_id');
            $table->boolean('is_volunteer')->default(false);
            $table->string('habit')->nullable();
            $table->string('bio')->nullable();
            $table->string('job')->nullable();
            $table->string('volunteer')->nullable();
            $table->string('origin')->nullable();
            $table->string('web')->nullable();
            $table->string('activity')->nullable();

            // $table->foreign('benevolent_type_id')->references('id')->on('benevole_types');
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
