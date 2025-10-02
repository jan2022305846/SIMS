<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * This migration creates the complete database schema based on the final SQL dump.
     */
    public function up(): void
    {
        // Create tables without foreign keys first
        Schema::create('cache', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->longText('value');
            $table->integer('expiration');
        });

        Schema::create('cache_locks', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->string('owner');
            $table->integer('expiration');
        });

        Schema::create('failed_jobs', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->unique();
            $table->text('connection');
            $table->text('queue');
            $table->longText('payload');
            $table->longText('exception');
            $table->timestamp('failed_at')->useCurrent();
        });

        Schema::create('jobs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('queue')->index();
            $table->longText('payload');
            $table->unsignedTinyInteger('attempts');
            $table->unsignedInteger('reserved_at')->nullable();
            $table->unsignedInteger('available_at');
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email', 150)->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        // Offices table (needed by users)
        Schema::create('offices', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('location')->nullable();
            $table->timestamps();
        });

        // Users table (references offices)
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('username', 100)->unique();
            $table->string('email', 150)->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->boolean('must_set_password')->default(1);
            $table->foreignId('office_id')->nullable()->constrained('offices')->onDelete('set null');
            $table->rememberToken();
            $table->timestamps();
        });

        // Categories table
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('type', 50)->default('general');
            $table->timestamps();
        });

        // Consumables table (references categories)
        Schema::create('consumables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('categories');
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('product_code')->unique();
            $table->integer('quantity');
            $table->string('unit')->default('pieces');
            $table->string('brand')->nullable();
            $table->integer('min_stock')->default(1);
            $table->integer('max_stock')->default(100);
            $table->softDeletes();
            $table->timestamps();
        });

        // Non-consumables table (references categories and users)
        Schema::create('non_consumables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('categories');
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('product_code')->unique();
            $table->integer('quantity');
            $table->string('unit')->default('pieces');
            $table->string('brand')->nullable();
            $table->integer('min_stock')->default(1);
            $table->integer('max_stock')->default(100);
            $table->foreignId('current_holder_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('location');
            $table->string('condition')->default('Good');
            $table->softDeletes();
            $table->timestamps();
        });

        // Requests table (references users and offices)
        Schema::create('requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('office_id')->nullable()->constrained('offices')->onDelete('set null');
            $table->unsignedBigInteger('item_id'); // Will be handled by application logic
            $table->enum('item_type', ['consumable', 'non_consumable']);
            $table->integer('quantity');
            $table->text('purpose');
            $table->date('needed_date')->nullable();
            $table->enum('status', ['pending', 'approved_by_admin', 'fulfilled', 'claimed', 'declined', 'returned'])->default('pending');
            $table->foreignId('approved_by_admin_id')->nullable()->constrained('users')->onDelete('set null');
            $table->enum('priority', ['low', 'normal', 'high', 'urgent'])->default('normal');
            $table->string('claim_slip_number')->nullable();
            $table->json('attachments')->nullable();
            $table->text('notes')->nullable();
            $table->date('return_date')->nullable();
            $table->timestamps();
        });

        // Logs table (references users)
        Schema::create('logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users');
            $table->unsignedBigInteger('item_id'); // Will be handled by application logic
            $table->enum('action', ['borrowed', 'returned', 'transferred', 'damaged', 'lost']);
            $table->text('details')->nullable();
            $table->integer('quantity')->default(1);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'action']);
            $table->index(['item_id', 'action']);
        });

        // Item scan logs table (references users and offices)
        Schema::create('item_scan_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('item_id'); // Will be handled by application logic
            $table->enum('item_type', ['consumable', 'non_consumable']);
            $table->foreignId('user_id')->constrained('users');
            $table->enum('action', ['check_in', 'check_out', 'inventory_check']);
            $table->foreignId('location_id')->nullable()->constrained('offices');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['item_id', 'user_id']);
            $table->index(['location_id']);
        });

        // Activity logs table (using Spatie Activity Log package)
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('log_name')->nullable();
            $table->text('description');
            $table->nullableMorphs('subject', 'subject');
            $table->nullableMorphs('causer', 'causer');
            $table->json('properties')->nullable();
            $table->string('batch_uuid')->nullable();
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('created_at');

            $table->index(['log_name', 'created_at']);
            $table->index(['subject_type', 'subject_id']);
            $table->index(['causer_type', 'causer_id']);
        });

        // Laravel Session table
        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });

        // Personal access tokens (Laravel Sanctum)
        Schema::create('personal_access_tokens', function (Blueprint $table) {
            $table->id();
            $table->morphs('tokenable');
            $table->string('name');
            $table->string('token', 64)->unique();
            $table->text('abilities')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop tables in reverse order to handle foreign key constraints
        Schema::dropIfExists('personal_access_tokens');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('cache_locks');
        Schema::dropIfExists('cache');
        Schema::dropIfExists('failed_jobs');
        Schema::dropIfExists('jobs');
        Schema::dropIfExists('activity_logs');
        Schema::dropIfExists('item_scan_logs');
        Schema::dropIfExists('logs');
        Schema::dropIfExists('requests');
        Schema::dropIfExists('non_consumables');
        Schema::dropIfExists('consumables');
        Schema::dropIfExists('categories');
        Schema::dropIfExists('offices');
        Schema::dropIfExists('users');
    }
};
