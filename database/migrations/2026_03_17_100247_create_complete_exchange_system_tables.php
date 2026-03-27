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



        // ============================================================
        // 2. CURRENCIES TABLE (no foreign keys)
        // ============================================================
        Schema::create('currencies', function (Blueprint $table) {
            $table->id();
            $table->string('code', 3)->unique();
            $table->string('name', 100);
            $table->string('symbol', 10)->nullable();
            $table->string('flag_icon', 50)->nullable();
            $table->integer('decimal_places')->default(2);
            $table->decimal('min_buy_rate', 15, 4)->nullable();
            $table->decimal('max_buy_rate', 15, 4)->nullable();
            $table->decimal('min_sell_rate', 15, 4)->nullable();
            $table->decimal('max_sell_rate', 15, 4)->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_base_currency')->default(false);
            $table->integer('display_order')->default(0);

            // Foreign keys (will be added after users exist)
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index('code');
            $table->index('is_active');
        });

        // ============================================================
        // 3. GOLD TYPES TABLE (no foreign keys)
        // ============================================================
        Schema::create('gold_types', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('name', 100);
            $table->enum('category', ['world', 'myanmar', 'other'])->default('myanmar');
            $table->string('purity', 50)->nullable();
            $table->string('unit', 50);
            $table->decimal('gram_conversion', 15, 4)->nullable();
            $table->string('description', 500)->nullable();
            $table->decimal('min_price', 15, 4)->nullable();
            $table->decimal('max_price', 15, 4)->nullable();
            $table->string('color_code', 7)->nullable();
            $table->string('icon', 50)->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('show_on_dashboard')->default(true);
            $table->integer('display_order')->default(0);
            $table->json('metadata')->nullable();

            // Foreign keys (will be added after users exist)
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index('category');
            $table->index('is_active');
        });

        // ============================================================
        // 4. EXCHANGE RATES TABLE (depends on currencies and users)
        // ============================================================
        Schema::create('exchange_rates', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('currency_id');
            $table->date('rate_date');
            $table->decimal('buy_rate', 15, 4);
            $table->decimal('sell_rate', 15, 4);
            $table->decimal('mid_rate', 15, 4)->storedAs('(buy_rate + sell_rate) / 2');
            $table->decimal('cbm_rate', 15, 4)->nullable();
            $table->decimal('previous_buy_rate', 15, 4)->nullable();
            $table->decimal('previous_sell_rate', 15, 4)->nullable();
            $table->decimal('change_percentage', 8, 4)->nullable();
            $table->enum('market_trend', ['up', 'down', 'stable'])->nullable();
            $table->text('market_analysis')->nullable();
            $table->json('factors')->nullable();
            $table->string('source_name', 100)->nullable();
            $table->string('source_reference', 100)->nullable();
            $table->enum('status', ['draft', 'pending', 'verified', 'rejected'])->default('draft');
            $table->boolean('is_verified')->default(false);
            $table->timestamp('verified_at')->nullable();
            $table->unsignedBigInteger('verified_by')->nullable();
            $table->text('verification_notes')->nullable();

            // Audit fields
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Unique constraint
            $table->unique(['currency_id', 'rate_date']);

            $table->index('rate_date');
            $table->index('status');
            $table->index('is_verified');
            $table->index(['currency_id', 'rate_date', 'is_verified']);
        });

        // ============================================================
        // 5. GOLD PRICES TABLE (depends on gold_types, currencies, users)
        // ============================================================
        Schema::create('gold_prices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('gold_type_id');
            $table->date('price_date');
            $table->decimal('price', 15, 4);
            $table->unsignedBigInteger('currency_id');
            $table->decimal('opening_price', 15, 4)->nullable();
            $table->decimal('closing_price', 15, 4)->nullable();
            $table->decimal('high_price', 15, 4)->nullable();
            $table->decimal('low_price', 15, 4)->nullable();
            $table->decimal('previous_price', 15, 4)->nullable();
            $table->decimal('change_percentage', 8, 4)->nullable();
            $table->decimal('world_gold_usd', 15, 4)->nullable();
            $table->decimal('usd_mmk_rate', 15, 4)->nullable();
            $table->enum('source_type', [
                'cso_official',
                'ygea_reference',
                'ygx',
                'market_actual',
                'international',
                'manual_entry',
                'aggregated'
            ])->default('manual_entry');
            $table->string('source_name', 100)->nullable();
            $table->string('source_location', 100)->nullable();
            $table->enum('trend', ['up', 'down', 'stable'])->nullable();
            $table->text('market_notes')->nullable();
            $table->json('market_factors')->nullable();
            $table->enum('status', ['draft', 'pending', 'verified', 'rejected'])->default('draft');
            $table->boolean('is_verified')->default(false);
            $table->timestamp('verified_at')->nullable();
            $table->unsignedBigInteger('verified_by')->nullable();
            $table->text('verification_notes')->nullable();

            // Audit fields
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Unique constraint
            $table->unique(['gold_type_id', 'price_date', 'source_type'], 'gold_unique_entry');

            $table->index('price_date');
            $table->index('status');
            $table->index('source_type');
            $table->index(['gold_type_id', 'price_date']);
        });

        // ============================================================
        // 6. MARKET UPDATES TABLE (depends on users)
        // ============================================================
        Schema::create('market_updates', function (Blueprint $table) {
            $table->id();
            $table->string('title', 200);
            $table->text('content');
            $table->string('summary', 500)->nullable();
            $table->enum('category', ['currency', 'gold', 'both', 'general'])->default('general');
            $table->enum('priority', ['low', 'normal', 'high', 'urgent'])->default('normal');
            $table->json('related_currencies')->nullable();
            $table->json('related_gold_types')->nullable();
            $table->enum('impact', ['positive', 'negative', 'neutral'])->nullable();
            $table->string('impact_description', 500)->nullable();
            $table->string('featured_image', 500)->nullable();
            $table->json('attachments')->nullable();
            $table->string('source_name', 100)->nullable();
            $table->string('source_url', 500)->nullable();
            $table->string('author', 100)->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_breaking')->default(false);
            $table->bigInteger('views_count')->default(0);

            // Audit fields
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index('published_at');
            $table->index('category');
            $table->index('is_featured');
            $table->index('is_breaking');
        });

        // ============================================================
        // 7. PRICE HISTORIES TABLE (polymorphic, depends on users)
        // ============================================================
        Schema::create('price_histories', function (Blueprint $table) {
            $table->id();
            $table->morphs('priceable'); // Creates priceable_type and priceable_id
            $table->json('old_data')->nullable();
            $table->json('new_data')->nullable();
            $table->string('field_changed', 100)->nullable();
            $table->decimal('old_value', 15, 4)->nullable();
            $table->decimal('new_value', 15, 4)->nullable();
            $table->enum('action', ['created', 'updated', 'deleted', 'verified', 'rejected'])->default('updated');
            $table->text('reason')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 500)->nullable();

            // Foreign key
            $table->unsignedBigInteger('user_id');

            $table->timestamps();


            $table->index('action');
            $table->index('created_at');
        });

        // ============================================================
        // 8. VERIFICATION QUEUE TABLE (polymorphic, depends on users)
        // ============================================================
        Schema::create('verification_queue', function (Blueprint $table) {
            $table->id();
            $table->morphs('verifiable');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->json('submitted_data');
            $table->text('submission_notes')->nullable();
            $table->enum('priority', ['low', 'normal', 'high'])->default('normal');
            $table->timestamp('due_date')->nullable();

            // Review fields
            $table->unsignedBigInteger('reviewed_by')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('review_notes')->nullable();
            $table->enum('review_action', ['approved', 'rejected', 'request_changes'])->nullable();
            $table->text('change_requests')->nullable();
            $table->boolean('is_revision')->default(false);
            $table->unsignedBigInteger('original_queue_id')->nullable();

            // Foreign keys
            $table->unsignedBigInteger('submitted_by');

            $table->timestamps();

            $table->index(['status', 'priority']);
            $table->index('due_date');
        });

        // ============================================================
        // NOW ADD ALL FOREIGN KEY CONSTRAINTS
        // ============================================================

        // Currencies foreign keys
        Schema::table('currencies', function (Blueprint $table) {
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
        });

        // Gold types foreign keys
        Schema::table('gold_types', function (Blueprint $table) {
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
        });

        // Exchange rates foreign keys
        Schema::table('exchange_rates', function (Blueprint $table) {
            $table->foreign('currency_id')->references('id')->on('currencies')->onDelete('restrict');
            $table->foreign('verified_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('restrict');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
        });

        // Gold prices foreign keys
        Schema::table('gold_prices', function (Blueprint $table) {
            $table->foreign('gold_type_id')->references('id')->on('gold_types')->onDelete('restrict');
            $table->foreign('currency_id')->references('id')->on('currencies')->onDelete('restrict');
            $table->foreign('verified_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('restrict');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
        });

        // Market updates foreign keys
        Schema::table('market_updates', function (Blueprint $table) {
            $table->foreign('created_by')->references('id')->on('users')->onDelete('restrict');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
        });

        // Price histories foreign keys
        Schema::table('price_histories', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->onDelete('restrict');
        });

        // Verification queue foreign keys
        Schema::table('verification_queue', function (Blueprint $table) {
            $table->foreign('reviewed_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('original_queue_id')->references('id')->on('verification_queue')->onDelete('set null');
            $table->foreign('submitted_by')->references('id')->on('users')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {

        // Drop tables in reverse order to handle foreign keys
        Schema::dropIfExists('verification_queue');
        Schema::dropIfExists('price_histories');
        Schema::dropIfExists('market_updates');
        Schema::dropIfExists('gold_prices');
        Schema::dropIfExists('exchange_rates');
        Schema::dropIfExists('gold_types');
        Schema::dropIfExists('currencies');
    }
};
