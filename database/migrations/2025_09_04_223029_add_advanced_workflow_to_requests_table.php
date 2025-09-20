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
            // Advanced workflow status enum
            if (!Schema::hasColumn('requests', 'workflow_status')) {
                $table->enum('workflow_status', [
                    'pending', 
                    'approved_by_office_head', 
                    'approved_by_admin', 
                    'fulfilled', 
                    'claimed', 
                    'declined_by_office_head',
                    'declined_by_admin'
                ])->default('pending')->after('status');
            }
            
            // Approval tracking
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
                $table->string('claim_slip_number')->nullable()->unique()->after('priority');
            }
            if (!Schema::hasColumn('requests', 'attachments')) {
                $table->json('attachments')->nullable()->after('claim_slip_number');
            }
            
            // Add foreign key constraints only if columns exist
            try {
                if (Schema::hasColumn('requests', 'approved_by_office_head_id')) {
                    $table->foreign('approved_by_office_head_id')->references('id')->on('users')->onDelete('set null');
                }
            } catch (\Exception $e) {
                // Foreign key might already exist
            }
            try {
                if (Schema::hasColumn('requests', 'approved_by_admin_id')) {
                    $table->foreign('approved_by_admin_id')->references('id')->on('users')->onDelete('set null');
                }
            } catch (\Exception $e) {
                // Foreign key might already exist
            }
            try {
                if (Schema::hasColumn('requests', 'fulfilled_by_id')) {
                    $table->foreign('fulfilled_by_id')->references('id')->on('users')->onDelete('set null');
                }
            } catch (\Exception $e) {
                // Foreign key might already exist
            }
            try {
                if (Schema::hasColumn('requests', 'claimed_by_id')) {
                    $table->foreign('claimed_by_id')->references('id')->on('users')->onDelete('set null');
                }
            } catch (\Exception $e) {
                // Foreign key might already exist
            }
            
            // Add indexes (skip if they already exist)
            try {
                $table->index(['workflow_status']);
            } catch (\Exception $e) {
                // Index might already exist
            }
            try {
                $table->index(['priority']);
            } catch (\Exception $e) {
                // Index might already exist
            }
            try {
                $table->index(['department']);
            } catch (\Exception $e) {
                // Index might already exist
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('requests', function (Blueprint $table) {
            $table->dropForeign(['approved_by_office_head_id']);
            $table->dropForeign(['approved_by_admin_id']);
            $table->dropForeign(['fulfilled_by_id']);
            $table->dropForeign(['claimed_by_id']);
            
            $table->dropIndex(['workflow_status']);
            $table->dropIndex(['priority']);
            $table->dropIndex(['department']);
            
            $table->dropColumn([
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
            ]);
        });
    }
};
