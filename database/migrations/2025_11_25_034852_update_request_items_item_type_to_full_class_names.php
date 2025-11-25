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
        // Update item_type values to full class names
        DB::table('request_items')->where('item_type', 'consumable')->update(['item_type' => 'App\\Models\\Consumable']);
        DB::table('request_items')->where('item_type', 'non_consumable')->update(['item_type' => 'App\\Models\\NonConsumable']);
    }

    public function down(): void
    {
        // Revert back to short names
        DB::table('request_items')->where('item_type', 'App\\Models\\Consumable')->update(['item_type' => 'consumable']);
        DB::table('request_items')->where('item_type', 'App\\Models\\NonConsumable')->update(['item_type' => 'non_consumable']);
    }
};
