<?php
// database/migrations/2026_04_20_000000_create_fuel_calibration_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        Schema::create('fuel_calibration', function (Blueprint $table) {
            $table->id();
            $table->decimal('calibration_factor', 8, 4)->default(1.4000);
            $table->integer('reference_price_92')->nullable();
            $table->decimal('global_price_at_calibration', 8, 4)->nullable();
            $table->decimal('usd_mmk_at_calibration', 10, 2)->nullable();
            $table->string('notes')->nullable();
            $table->timestamps();
        });

        DB::table('fuel_calibration')->insert([
            'calibration_factor' => 1.4000,
            'reference_price_92' => 4735,
            'notes' => 'Initial calibration - April 2026',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down()
    {
        Schema::dropIfExists('fuel_calibration');
    }
};
