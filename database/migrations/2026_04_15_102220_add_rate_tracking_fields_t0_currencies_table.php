<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('currencies', function (Blueprint $table) {
            if (!Schema::hasColumn('currencies', 'last_sync_rate')) {
                $table->decimal('last_sync_rate', 15, 4)->nullable()->after('cbm_rate');
            }
            if (!Schema::hasColumn('currencies', 'last_synced_at')) {
                $table->timestamp('last_synced_at')->nullable()->after('last_sync_rate');
            }
            if (!Schema::hasColumn('currencies', 'rate_confidence')) {
                $table->decimal('rate_confidence', 5, 4)->default(0)->after('last_synced_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('currencies', function (Blueprint $table) {
            $table->dropColumn(['last_sync_rate', 'last_synced_at', 'rate_confidence']);
        });
    }
};
