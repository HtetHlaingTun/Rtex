<?php
// database/migrations/2024_01_01_000010_add_cbm_fields_to_currencies.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('currencies', function (Blueprint $table) {
            // CBM Integration Fields
            $table->decimal('cbm_conversion_factor', 12, 6)->nullable()->after('max_sell_rate')
                ->comment('Manual factor to convert CBM rate to working rate');

            $table->decimal('buy_spread_percentage', 8, 4)->default(0.5)->after('cbm_conversion_factor')
                ->comment('Buy spread percentage');

            $table->decimal('sell_spread_percentage', 8, 4)->default(0.5)->after('buy_spread_percentage')
                ->comment('Sell spread percentage');

            $table->decimal('fixed_buy_margin', 15, 2)->nullable()->after('sell_spread_percentage')
                ->comment('Fixed buy margin in MMK');

            $table->decimal('fixed_sell_margin', 15, 2)->nullable()->after('fixed_buy_margin')
                ->comment('Fixed sell margin in MMK');

            $table->enum('spread_type', ['percentage', 'fixed'])->default('percentage')->after('fixed_sell_margin')
                ->comment('Spread calculation type');

            $table->boolean('use_cbm_auto_fetch')->default(true)->after('spread_type')
                ->comment('Auto-fetch from CBM API');

            $table->timestamp('factor_last_updated')->nullable()->after('use_cbm_auto_fetch');
            $table->unsignedBigInteger('factor_updated_by')->nullable()->after('factor_last_updated');

            $table->foreign('factor_updated_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('currencies', function (Blueprint $table) {
            $table->dropForeign(['factor_updated_by']);
            $table->dropColumn([
                'cbm_conversion_factor',
                'buy_spread_percentage',
                'sell_spread_percentage',
                'fixed_buy_margin',
                'fixed_sell_margin',
                'spread_type',
                'use_cbm_auto_fetch',
                'factor_last_updated',
                'factor_updated_by'
            ]);
        });
    }
};
