<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('fuel_prices', function (Blueprint $table) {
            // Add premium_diesel column after diesel
            $table->integer('premium_diesel')->nullable()->after('diesel');
            // Add change percent for premium diesel
            $table->decimal('change_percent_premium_diesel', 5, 2)->nullable()->after('change_percent_diesel');
        });
    }

    public function down()
    {
        Schema::table('fuel_prices', function (Blueprint $table) {
            $table->dropColumn(['premium_diesel', 'change_percent_premium_diesel']);
        });
    }
};
