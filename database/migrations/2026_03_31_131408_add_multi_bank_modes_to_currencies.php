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
            // Mode Selector: cbm, bank_avg, manual
            $table->string('source_mode')->default('cbm')->after('code');

            // Manual Mode Input
            $table->decimal('manual_base_rate', 15, 4)->nullable()->after('source_mode');

            // Reference rates from specific banks
            $table->decimal('rate_kbz', 15, 4)->nullable();
            $table->decimal('rate_yoma', 15, 4)->nullable();
            $table->decimal('rate_cb', 15, 4)->nullable();
            $table->decimal('rate_aya', 15, 4)->nullable();

            // Calculated Average and Premium
            $table->decimal('avg_bank_rate', 15, 4)->nullable();
            $table->decimal('bank_markup_percentage', 5, 2)->default(15.00);
            $table->timestamp('banks_last_synced_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('currencies', function (Blueprint $table) {
            $table->dropColumn('source_mode');


            $table->dropColumn('manual_base_rate');


            $table->dropColumn('rate_kbz');
            $table->dropColumn('rate_yoma');
            $table->dropColumn('rate_cb');
            $table->dropColumn('rate_aya');


            $table->dropColumn('avg_bank_rate');
            $table->dropColumn('bank_markup_percentage');
            $table->dropColumn('banks_last_synced_at');
        });
    }
};
