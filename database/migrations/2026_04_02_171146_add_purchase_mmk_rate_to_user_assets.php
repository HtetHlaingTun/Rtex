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
        Schema::table('user_assets', function (Blueprint $table) {
            $table->decimal('purchase_mmk_rate', 15, 4)->nullable()->after('purchase_sgd_rate');
        });
    }

    public function down()
    {
        Schema::table('user_assets', function (Blueprint $table) {
            $table->dropColumn('purchase_mmk_rate');
        });
    }
};
