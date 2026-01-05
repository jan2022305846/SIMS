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
        Schema::table('request_items', function (Blueprint $table) {
            $table->integer('adjusted_quantity')->nullable()->after('quantity');
            $table->text('adjustment_reason')->nullable()->after('adjusted_quantity');
            $table->enum('item_status', ['pending', 'approved', 'declined', 'fulfilled'])->default('pending')->after('adjustment_reason');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('request_items', function (Blueprint $table) {
            $table->dropColumn(['adjusted_quantity', 'adjustment_reason', 'item_status']);
        });
    }
};
