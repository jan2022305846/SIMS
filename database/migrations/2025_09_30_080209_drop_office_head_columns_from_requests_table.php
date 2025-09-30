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
        Schema::table('requests', function (Blueprint $table) {
            // Check if columns exist before dropping them
            $columns = Schema::getColumnListing('requests');
            
            $columnsToDrop = [];
            if (in_array('approved_by_office_head_id', $columns)) {
                $columnsToDrop[] = 'approved_by_office_head_id';
            }
            if (in_array('office_head_approval_date', $columns)) {
                $columnsToDrop[] = 'office_head_approval_date';
            }
            if (in_array('office_head_notes', $columns)) {
                $columnsToDrop[] = 'office_head_notes';
            }
            
            if (!empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('requests', function (Blueprint $table) {
            // Check if columns don't exist before adding them
            $columns = Schema::getColumnListing('requests');
            
            if (!in_array('approved_by_office_head_id', $columns)) {
                $table->unsignedBigInteger('approved_by_office_head_id')->nullable()->after('approved_by_admin_id');
                $table->foreign('approved_by_office_head_id')->references('id')->on('users');
            }
            
            if (!in_array('office_head_approval_date', $columns)) {
                $table->timestamp('office_head_approval_date')->nullable()->after('approved_by_office_head_id');
            }
            
            if (!in_array('office_head_notes', $columns)) {
                $table->text('office_head_notes')->nullable()->after('office_head_approval_date');
            }
        });
    }
};
