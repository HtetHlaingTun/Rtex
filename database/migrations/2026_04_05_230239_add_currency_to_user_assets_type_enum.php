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
        DB::statement("ALTER TABLE user_assets MODIFY COLUMN type ENUM('gold', 'property', 'car', 'jewelry', 'crypto', 'currency', 'other') NOT NULL");
    }

    public function down()
    {
        DB::statement("ALTER TABLE user_assets MODIFY COLUMN type ENUM('gold', 'property', 'car', 'jewelry', 'crypto', 'other') NOT NULL");
    }
};
