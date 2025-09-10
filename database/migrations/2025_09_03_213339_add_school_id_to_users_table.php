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
            if (!Schema::hasColumn('users', 'school_id')) {
                $table->string('school_id')->unique()->after('email');
            }
            if (!Schema::hasColumn('users', 'department')) {
                $table->string('department')->nullable()->after('email');
            }
            if (!Schema::hasColumn('users', 'role')) {
                $table->enum('role', ['admin', 'faculty'])->default('faculty')->after('department');
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
