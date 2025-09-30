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
            // Drop the existing location column
            $table->dropColumn('location');
            
            // Add new location column as foreign key to offices
            $table->unsignedBigInteger('location_id')->nullable()->after('action');
            $table->foreign('location_id')->references('id')->on('offices');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('item_scan_logs', function (Blueprint $table) {
            // Drop the foreign key and location_id column
            $table->dropForeign(['location_id']);
            $table->dropColumn('location_id');
            
            // Restore the original location column
            $table->string('location')->nullable()->after('action');
        });
    }
};
