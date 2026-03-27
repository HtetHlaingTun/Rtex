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
        Schema::table('world_gold_snapshots', function (Blueprint $table) {
            $table->decimal('mmk_price_new', 14, 2)->nullable()->after('mmk_price'); // 16.329g
            $table->decimal('mmk_price_old', 14, 2)->nullable()->after('mmk_price_new'); // 16.606g
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('world_gold_snapshots', function (Blueprint $table) {
            $table->dropColumn(['mmk_price_new', 'mmk_price_old']);
        });
    }
};
