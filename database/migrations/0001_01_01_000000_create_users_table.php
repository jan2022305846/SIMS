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
            $table->string('username', 100)->unique(); // Reduced length for older MySQL compatibility
            $table->string('email', 150)->unique(); // Reduced length for older MySQL compatibility
            $table->string('school_id', 50)->unique()->nullable(); // Reduced length
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->enum('role', ['admin', 'office_head', 'faculty'])->default('faculty'); // Include all roles
            $table->string('department', 100)->nullable(); // Reduced length
            $table->unsignedBigInteger('office_id')->nullable(); // Add office_id for future compatibility
            $table->rememberToken();
            $table->timestamps();

            // Add indexes for performance (with shorter lengths for compatibility)
            $table->index(['role']);
            $table->index(['school_id']);
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email', 150)->primary(); // Reduced length
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
