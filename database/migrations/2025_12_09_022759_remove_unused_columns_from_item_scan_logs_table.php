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
            $table->dropForeign(['user_id']);
            $table->dropColumn(['item_type', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('item_scan_logs', function (Blueprint $table) {
            $table->enum('item_type', ['consumable', 'non_consumable']);
            $table->foreignId('user_id')->constrained('users');
        });
    }
};
