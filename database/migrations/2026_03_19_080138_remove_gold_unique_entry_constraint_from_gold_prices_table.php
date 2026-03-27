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
        Schema::table('gold_prices', function (Blueprint $table) {
            $table->dropUnique('gold_unique_entry');
        });
    }

    public function down(): void
    {
        Schema::table('gold_prices', function (Blueprint $table) {
            $table->unique(['gold_type_id', 'price_date', 'source_type'], 'gold_unique_entry');
        });
    }
};
