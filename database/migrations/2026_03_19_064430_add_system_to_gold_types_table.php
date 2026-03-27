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
        Schema::table('gold_types', function (Blueprint $table) {
            // 'new' = 16.329g per kyatthar
            // 'old' = 16.606g per kyatthar
            $table->enum('system', ['new', 'old'])->default('new')->after('category');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('gold_types', function (Blueprint $table) {
            $table->dropColumn('system');
        });
    }
};
