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
        // This migration has been superseded by 2025_09_06_174232_add_missing_columns_to_items_table
        // which includes these columns and more. Skip to avoid conflicts.
        return;
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This migration was superseded, so no action needed on rollback
        return;
    }
};
