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
        Schema::create('world_gold_snapshots', function (Blueprint $table) {
            $table->id();
            $table->decimal('usd_price', 10, 2);
            $table->decimal('change', 10, 2)->default(0);
            $table->decimal('change_percent', 8, 4)->default(0);
            $table->decimal('day_high', 10, 2)->nullable();
            $table->decimal('day_low', 10, 2)->nullable();
            $table->decimal('previous_close', 10, 2)->nullable();
            // MMK conversion — pulled from exchange_rates at time of snapshot
            $table->decimal('usd_mmk_rate', 10, 2)->nullable();
            $table->decimal('mmk_price', 14, 2)->nullable(); // converted gold price
            $table->timestamp('fetched_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('world_gold_snapshots');
    }
};
