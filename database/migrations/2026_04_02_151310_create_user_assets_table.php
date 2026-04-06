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
        Schema::create('user_assets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('name'); // "Gold Bar", "Toyota Camry", "Condo Unit 5B"
            $table->enum('type', ['gold', 'property', 'car', 'jewelry', 'crypto', 'other']);
            $table->decimal('quantity', 15, 4)->default(1); // For gold: 1 kyatthar, for property: 1 unit
            $table->decimal('purchase_price', 15, 2);
            $table->string('purchase_currency', 3); // USD, SGD, MMK
            $table->decimal('purchase_usd_rate', 10, 4)->nullable(); // USD/MMK rate at purchase
            $table->decimal('purchase_sgd_rate', 10, 4)->nullable(); // SGD/MMK rate at purchase
            $table->date('purchase_date');
            $table->text('description')->nullable();
            $table->json('metadata')->nullable(); // Store additional details like car model, gold purity
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['user_id', 'type']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('user_assets');
    }
};
