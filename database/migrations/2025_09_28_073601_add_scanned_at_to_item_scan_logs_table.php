<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('item_scan_logs', function (Blueprint $table) {
            // Add missing columns that the model expects
            if (!Schema::hasColumn('item_scan_logs', 'scanned_at')) {
                $table->timestamp('scanned_at')->default(DB::raw('created_at'))->after('user_id');
            }
            if (!Schema::hasColumn('item_scan_logs', 'scanner_type')) {
                $table->string('scanner_type')->default('admin')->after('location');
            }
            if (!Schema::hasColumn('item_scan_logs', 'scan_data')) {
                $table->json('scan_data')->nullable()->after('scanner_type');
            }
            if (!Schema::hasColumn('item_scan_logs', 'ip_address')) {
                $table->string('ip_address')->nullable()->after('scan_data');
            }
            if (!Schema::hasColumn('item_scan_logs', 'user_agent')) {
                $table->text('user_agent')->nullable()->after('ip_address');
            }

            // Add indexes if they don't exist
            try {
                $table->index(['item_id', 'scanned_at']);
            } catch (\Exception $e) {
                // Index might already exist
            }
            try {
                $table->index(['scanned_at']);
            } catch (\Exception $e) {
                // Index might already exist
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('item_scan_logs', function (Blueprint $table) {
            $table->dropIndex(['item_id', 'scanned_at']);
            $table->dropIndex(['scanned_at']);
            $table->dropColumn(['scanned_at', 'scanner_type', 'scan_data', 'ip_address', 'user_agent']);
        });
    }
};
