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
        Schema::create('fuel_prices', function (Blueprint $table) {
            $table->id();
            $table->integer('octane_92');
            $table->integer('octane_95');
            $table->integer('diesel');
            $table->decimal('global_usd_reference', 8, 4); // Store the RBOB price
            $table->integer('market_usd_rate'); // Store the MMK rate used
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fuel_prices');
    }
};
