<?php
// database/migrations/xxxx_add_region_and_trends_to_fuel_prices_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('fuel_prices', function (Blueprint $table) {
            // Add region column first (default to yangon for existing records)
            $table->string('region')->default('yangon')->after('id');

            // Add trend percentage columns
            $table->decimal('change_percent_92', 8, 2)->default(0)->after('diesel');
            $table->decimal('change_percent_95', 8, 2)->default(0)->after('change_percent_92');
            $table->decimal('change_percent_diesel', 8, 2)->default(0)->after('change_percent_95');

            // Add index for faster queries
            $table->index(['region', 'created_at']);
        });
    }

    public function down()
    {
        Schema::table('fuel_prices', function (Blueprint $table) {
            $table->dropColumn(['region', 'change_percent_92', 'change_percent_95', 'change_percent_diesel']);
            $table->dropIndex(['region', 'created_at']);
        });
    }
};
