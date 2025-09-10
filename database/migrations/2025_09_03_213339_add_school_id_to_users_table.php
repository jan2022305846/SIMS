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
        Schema::table('users', function (Blueprint $table) {
            // Only add school_id if it doesn't exist (for backward compatibility)
            if (!Schema::hasColumn('users', 'school_id')) {
                $table->string('school_id')->unique()->nullable()->after('email');
            }
            
            // Update role column to include office_head if not already present
            if (Schema::hasColumn('users', 'role')) {
                // Check current column type and update if needed
                $table->enum('role', ['admin', 'office_head', 'faculty'])->default('faculty')->change();
            }
            
            // Add office_id if it doesn't exist
            if (!Schema::hasColumn('users', 'office_id')) {
                $table->unsignedBigInteger('office_id')->nullable()->after('department');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['school_id', 'username', 'department', 'role']);
        });
    }
};
