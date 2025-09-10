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
        Schema::table('item_scan_logs', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('item_id')->constrained('users')->onDelete('set null');
            $table->index(['user_id', 'scanned_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('item_scan_logs', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropIndex(['user_id', 'scanned_at']);
            $table->dropColumn('user_id');
        });
    }
};
