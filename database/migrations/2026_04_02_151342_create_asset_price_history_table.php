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
        Schema::create('asset_price_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_asset_id')->constrained()->onDelete('cascade');
            $table->decimal('price', 15, 2);
            $table->string('currency', 3);
            $table->decimal('usd_rate', 10, 4)->nullable();
            $table->decimal('sgd_rate', 10, 4)->nullable();
            $table->decimal('mmk_price', 15, 2)->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('asset_price_history');
    }
};
