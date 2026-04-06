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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');

            // Role & Permissions
            $table->enum('role', ['super_admin', 'admin', 'editor', 'viewer',])->default('viewer');
            $table->json('permissions')->nullable(); // For fine-grained permissions

            // Profile Information
            $table->string('profile_photo')->nullable();
            $table->string('phone')->nullable();
            $table->string('department')->nullable(); // Which department they work in

            // Account Status
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_login_at')->nullable();
            $table->string('last_login_ip')->nullable();
            $table->string('last_login_user_agent')->nullable();

            // Email Notification Preferences
            $table->boolean('notify_on_verification')->default(true);
            $table->boolean('notify_on_new_entry')->default(false);
            $table->boolean('notify_on_rejection')->default(true);

            // 2FA (Optional - for security)
            $table->boolean('two_factor_enabled')->default(false);
            $table->string('two_factor_secret')->nullable();
            $table->json('two_factor_recovery_codes')->nullable();

            // Password Reset & Remember Token
            $table->rememberToken();
            $table->timestamp('password_changed_at')->nullable();

            // Activity Tracking
            $table->integer('login_count')->default(0);
            $table->integer('entries_created_count')->default(0);
            $table->integer('entries_verified_count')->default(0);

            // Audit Fields
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');

            $table->timestamps();
            $table->softDeletes(); // So you can recover accidentally deleted users

            // Indexes for faster queries
            $table->index(['role', 'is_active']);
            $table->index('email');
            $table->index('last_login_at');
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
