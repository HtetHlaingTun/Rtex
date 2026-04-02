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
        Schema::table('currencies', function (Blueprint $table) {
            // Dropping the bank-specific columns
            $table->dropColumn([
                'rate_kbz',
                'rate_yoma',
                'rate_cb',
                'rate_aya',
                // Keep 'avg_bank_rate' if you want a "Live" cache, 
                // but if you want it ONLY in history, remove it here too:

            ]);
        });
    }

    /**
     * Reverse the migrations (Put them back if needed).
     */
    public function down(): void
    {
        Schema::table('currencies', function (Blueprint $table) {
            $table->decimal('rate_kbz', 15, 4)->nullable();
            $table->decimal('rate_yoma', 15, 4)->nullable();
            $table->decimal('rate_cb', 15, 4)->nullable();
            $table->decimal('rate_aya', 15, 4)->nullable();
        });
    }
};
