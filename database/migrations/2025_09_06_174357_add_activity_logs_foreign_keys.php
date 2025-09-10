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
        Schema::table('activity_logs', function (Blueprint $table) {
            // Add foreign key constraint for causer_id when causer_type is App\Models\User
            // Note: This is a soft constraint since activity_logs can have different causer_types
            $table->index(['causer_type', 'causer_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('activity_logs', function (Blueprint $table) {
            $table->dropIndex(['causer_type', 'causer_id']);
        });
    }
};
