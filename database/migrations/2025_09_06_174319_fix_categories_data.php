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
        // Fix incorrect category types
        DB::table('categories')
            ->where('name', 'Consumable')
            ->update(['type' => 'consumable']);
            
        // Ensure Non-Consumable is correct (should already be correct)
        DB::table('categories')
            ->where('name', 'Non-Consumable')
            ->update(['type' => 'non-consumable']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert the changes if needed
        DB::table('categories')
            ->where('name', 'Consumable')
            ->update(['type' => 'non-consumable']);
    }
};
