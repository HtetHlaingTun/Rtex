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
        Schema::table('user_assets', function (Blueprint $table) {
            // Gold-specific fields
            $table->enum('weight_unit', ['oz', 'gram', 'kyatthar'])->nullable()->after('quantity');
            $table->enum('kyatthar_type', ['new', 'old'])->nullable()->after('weight_unit');
            $table->enum('product_type', ['1oz', '50g', '100g', '1kyatthar', '10kyatthar', 'custom'])->nullable()->after('kyatthar_type');
            $table->decimal('weight_in_grams', 12, 4)->nullable()->after('product_type');
            $table->decimal('troy_ounces', 12, 6)->nullable()->after('weight_in_grams');

            // Index for faster queries
            $table->index(['user_id', 'product_type']);
        });
    }

    public function down()
    {
        Schema::table('user_assets', function (Blueprint $table) {
            $table->dropColumn([
                'weight_unit',
                'kyatthar_type',
                'product_type',
                'weight_in_grams',
                'troy_ounces'
            ]);
        });
    }
};
