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
            if (!Schema::hasColumn('item_scan_logs', 'item_type')) {
                $table->enum('item_type', ['consumable', 'non_consumable'])->after('item_id');
            }
            if (!Schema::hasColumn('item_scan_logs', 'notes')) {
                $table->text('notes')->nullable()->after('location_id');
            }
            
            // Update the action enum to include more actions
            DB::statement("ALTER TABLE item_scan_logs MODIFY COLUMN action ENUM('check_in', 'check_out', 'inventory_check', 'updated', 'item_claim', 'item_fulfill', 'stock_adjustment') NOT NULL");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('item_scan_logs', function (Blueprint $table) {
            $table->dropColumn(['item_type', 'notes']);
            
            // Revert the action enum
            DB::statement("ALTER TABLE item_scan_logs MODIFY COLUMN action ENUM('updated', 'inventory_check') NOT NULL");
        });
    }
};
