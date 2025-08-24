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
        Schema::table('enfilers', function (Blueprint $table) {
            $table->timestamp('approved_at')->nullable()->after('is_active');
            $table->timestamp('rejected_at')->nullable()->after('approved_at');
            $table->text('rejection_reason')->nullable()->after('rejected_at');
            $table->boolean('is_approved')->default(false)->after('is_active');
            $table->boolean('is_rejected')->default(false)->after('is_approved');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('enfilers', function (Blueprint $table) {
            $table->dropColumn('approved_at');
            $table->dropColumn('rejected_at');
            $table->dropColumn('rejection_reason');
            $table->dropColumn('is_approved');
            $table->dropColumn('is_rejected');
        });
    }
};
