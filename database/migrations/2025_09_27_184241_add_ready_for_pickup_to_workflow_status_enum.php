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
        // Change the enum to include 'ready_for_pickup'
        DB::statement("ALTER TABLE requests MODIFY COLUMN workflow_status ENUM('pending', 'approved_by_office_head', 'approved_by_admin', 'ready_for_pickup', 'fulfilled', 'claimed', 'declined_by_office_head', 'declined_by_admin') DEFAULT 'pending'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove 'ready_for_pickup' from the enum
        DB::statement("ALTER TABLE requests MODIFY COLUMN workflow_status ENUM('pending', 'approved_by_office_head', 'approved_by_admin', 'fulfilled', 'claimed', 'declined_by_office_head', 'declined_by_admin') DEFAULT 'pending'");
    }
};
