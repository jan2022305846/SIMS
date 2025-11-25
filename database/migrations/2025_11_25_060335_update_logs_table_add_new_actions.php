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
        // Update the action enum to include new actions for request claiming
        DB::statement("ALTER TABLE logs MODIFY COLUMN action ENUM('borrowed', 'returned', 'transferred', 'damaged', 'lost', 'claimed', 'assigned') NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert the action enum to original values
        DB::statement("ALTER TABLE logs MODIFY COLUMN action ENUM('borrowed', 'returned', 'transferred', 'damaged', 'lost') NOT NULL");
    }
};
