<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCbmRateToCurrencies extends Migration
{
    public function up()
    {
        Schema::table('currencies', function (Blueprint $table) {
            $table->decimal('cbm_rate', 15, 4)->nullable()->after('avg_bank_rate');
        });
    }

    public function down()
    {
        Schema::table('currencies', function (Blueprint $table) {
            $table->dropColumn('cbm_rate');
        });
    }
}
