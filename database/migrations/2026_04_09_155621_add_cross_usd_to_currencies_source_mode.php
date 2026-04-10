<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        // For MySQL
        DB::statement("ALTER TABLE currencies MODIFY COLUMN source_mode ENUM('manual', 'bank_avg', 'cbm', 'cross_usd') NOT NULL DEFAULT 'bank_avg'");
    }

    public function down()
    {
        DB::statement("ALTER TABLE currencies MODIFY COLUMN source_mode ENUM('manual', 'bank_avg', 'cbm') NOT NULL DEFAULT 'bank_avg'");
    }
};
