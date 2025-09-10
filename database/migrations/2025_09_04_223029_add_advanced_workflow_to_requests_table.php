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
            $table->enum('workflow_status', [
                'pending', 
                'approved_by_office_head', 
                'approved_by_admin', 
                'fulfilled', 
                'claimed', 
                'declined_by_office_head',
                'declined_by_admin'
            ])->default('pending')->after('status');
            
            // Approval tracking
            $table->unsignedBigInteger('approved_by_office_head_id')->nullable()->after('workflow_status');
            $table->unsignedBigInteger('approved_by_admin_id')->nullable()->after('approved_by_office_head_id');
            $table->unsignedBigInteger('fulfilled_by_id')->nullable()->after('approved_by_admin_id');
            $table->unsignedBigInteger('claimed_by_id')->nullable()->after('fulfilled_by_id');
            
            // Workflow timestamps
            $table->timestamp('office_head_approval_date')->nullable()->after('claimed_by_id');
            $table->timestamp('admin_approval_date')->nullable()->after('office_head_approval_date');
            $table->timestamp('fulfilled_date')->nullable()->after('admin_approval_date');
            $table->timestamp('claimed_date')->nullable()->after('fulfilled_date');
            
            // Enhanced information
            $table->string('department')->nullable()->after('claimed_date');
            $table->text('office_head_notes')->nullable()->after('department');
            $table->enum('priority', ['low', 'normal', 'high', 'urgent'])->default('normal')->after('office_head_notes');
            $table->string('claim_slip_number')->nullable()->unique()->after('priority');
            $table->json('attachments')->nullable()->after('claim_slip_number');
            
            // Foreign key constraints
            $table->foreign('approved_by_office_head_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('approved_by_admin_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('fulfilled_by_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('claimed_by_id')->references('id')->on('users')->onDelete('set null');
            
            // Indexes for performance
            $table->index(['workflow_status']);
            $table->index(['priority']);
            $table->index(['department']);
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
