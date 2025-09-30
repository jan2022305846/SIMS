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
        // Update status enum to remove office head references
        DB::statement("ALTER TABLE requests MODIFY COLUMN status ENUM('pending','approved_by_admin','fulfilled','claimed','declined','returned') NOT NULL DEFAULT 'pending'");
        
        // Update workflow_status enum to remove office head references
        DB::statement("ALTER TABLE requests MODIFY COLUMN workflow_status ENUM('pending','approved_by_admin','fulfilled','claimed','declined_by_admin') NOT NULL DEFAULT 'pending'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Restore original enum values
        DB::statement("ALTER TABLE requests MODIFY COLUMN status ENUM('pending','approved_by_office_head','approved_by_admin','fulfilled','claimed','declined','approved','returned') NOT NULL DEFAULT 'pending'");
        DB::statement("ALTER TABLE requests MODIFY COLUMN workflow_status ENUM('pending','approved_by_office_head','approved_by_admin','fulfilled','claimed','declined_by_office_head','declined_by_admin') NOT NULL DEFAULT 'pending'");
    }
};
