<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */

    public function up()
    {
        Schema::table('world_gold_snapshots', function (Blueprint $table) {
            $table->decimal('sgd_price', 15, 2)->nullable()->after('usd_price');
            $table->decimal('usd_sgd_rate', 15, 4)->nullable()->after('sgd_price');
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('world_gold_snapshots', function (Blueprint $table) {
            $table->dropColumn('sgd_price');
            $table->dropColumn('usd_sgd_price');
        });
    }
};
