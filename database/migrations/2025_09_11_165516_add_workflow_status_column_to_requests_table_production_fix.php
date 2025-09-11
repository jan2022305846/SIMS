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
        // Check if workflow_status column already exists
        if (!Schema::hasColumn('requests', 'workflow_status')) {
            Schema::table('requests', function (Blueprint $table) {
                // Add workflow_status column
                $table->enum('workflow_status', [
                    'pending', 
                    'approved_by_office_head', 
                    'approved_by_admin', 
                    'fulfilled', 
                    'claimed', 
                    'declined_by_office_head',
                    'declined_by_admin'
                ])->default('pending')->after('status');
            });
            
            // Update existing records based on current status
            DB::statement("
                UPDATE requests 
                SET workflow_status = CASE 
                    WHEN status = 'approved' THEN 'approved_by_admin'
                    WHEN status = 'fulfilled' THEN 'fulfilled'
                    WHEN status = 'claimed' THEN 'claimed'
                    WHEN status = 'declined' THEN 'declined_by_admin'
                    ELSE 'pending'
                END
            ");
        }
        
        // Add other workflow-related columns if they don't exist
        Schema::table('requests', function (Blueprint $table) {
            if (!Schema::hasColumn('requests', 'approved_by_office_head_id')) {
                $table->unsignedBigInteger('approved_by_office_head_id')->nullable()->after('workflow_status');
            }
            if (!Schema::hasColumn('requests', 'approved_by_admin_id')) {
                $table->unsignedBigInteger('approved_by_admin_id')->nullable()->after('approved_by_office_head_id');
            }
            if (!Schema::hasColumn('requests', 'fulfilled_by_id')) {
                $table->unsignedBigInteger('fulfilled_by_id')->nullable()->after('approved_by_admin_id');
            }
            if (!Schema::hasColumn('requests', 'claimed_by_id')) {
                $table->unsignedBigInteger('claimed_by_id')->nullable()->after('fulfilled_by_id');
            }
            
            // Workflow timestamps
            if (!Schema::hasColumn('requests', 'office_head_approval_date')) {
                $table->timestamp('office_head_approval_date')->nullable()->after('claimed_by_id');
            }
            if (!Schema::hasColumn('requests', 'admin_approval_date')) {
                $table->timestamp('admin_approval_date')->nullable()->after('office_head_approval_date');
            }
            if (!Schema::hasColumn('requests', 'fulfilled_date')) {
                $table->timestamp('fulfilled_date')->nullable()->after('admin_approval_date');
            }
            if (!Schema::hasColumn('requests', 'claimed_date')) {
                $table->timestamp('claimed_date')->nullable()->after('fulfilled_date');
            }
            
            // Enhanced information
            if (!Schema::hasColumn('requests', 'department')) {
                $table->string('department')->nullable()->after('claimed_date');
            }
            if (!Schema::hasColumn('requests', 'office_head_notes')) {
                $table->text('office_head_notes')->nullable()->after('department');
            }
            if (!Schema::hasColumn('requests', 'priority')) {
                $table->enum('priority', ['low', 'normal', 'high', 'urgent'])->default('normal')->after('office_head_notes');
            }
            if (!Schema::hasColumn('requests', 'claim_slip_number')) {
                $table->string('claim_slip_number')->nullable()->after('priority');
            }
            if (!Schema::hasColumn('requests', 'attachments')) {
                $table->json('attachments')->nullable()->after('claim_slip_number');
            }
        });
        
        // Add indexes for better performance
        try {
            Schema::table('requests', function (Blueprint $table) {
                $table->index(['workflow_status'], 'requests_workflow_status_index');
                $table->index(['priority'], 'requests_priority_index');
                $table->index(['department'], 'requests_department_index');
            });
        } catch (\Exception $e) {
            // Indexes may already exist, ignore errors
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('requests', function (Blueprint $table) {
            // Drop indexes
            try {
                $table->dropIndex(['workflow_status']);
                $table->dropIndex(['priority']);
                $table->dropIndex(['department']);
            } catch (\Exception $e) {
                // Ignore if indexes don't exist
            }
            
            // Drop columns
            $columns = [
                'workflow_status',
                'approved_by_office_head_id',
                'approved_by_admin_id',
                'fulfilled_by_id',
                'claimed_by_id',
                'office_head_approval_date',
                'admin_approval_date',
                'fulfilled_date',
                'claimed_date',
                'department',
                'office_head_notes',
                'priority',
                'claim_slip_number',
                'attachments'
            ];
            
            foreach ($columns as $column) {
                if (Schema::hasColumn('requests', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
