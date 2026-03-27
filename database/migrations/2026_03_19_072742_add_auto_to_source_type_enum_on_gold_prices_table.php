<?php

use Illuminate\Database\Migrations\Migration;

use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE gold_prices MODIFY COLUMN source_type ENUM('cso_official','ygea_reference','ygx','market_actual','international','manual_entry','aggregated','auto') DEFAULT 'manual_entry'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE gold_prices MODIFY COLUMN source_type ENUM('cso_official','ygea_reference','ygx','market_actual','international','manual_entry','aggregated') DEFAULT 'manual_entry'");
    }
};
