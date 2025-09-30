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
            $table->dropForeign('requests_fulfilled_by_id_foreign');
            $table->dropForeign('requests_claimed_by_id_foreign');
            $table->dropColumn([
                'quantity_requested',
                'workflow_status',
                'fulfilled_by_id',
                'claimed_by_id',
                'request_date',
                'admin_approval_date',
                'fulfilled_date',
                'claimed_date',
                'fulfillment_date',
                'claim_date',
                'approval_date',
                'decline_reason',
                'remarks',
                'admin_notes'
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('requests', function (Blueprint $table) {
            $table->integer('quantity_requested')->after('quantity');
            $table->enum('workflow_status', ['pending', 'approved_by_admin', 'fulfilled', 'claimed', 'declined_by_admin'])->default('pending')->after('status');
            $table->unsignedBigInteger('fulfilled_by_id')->nullable()->after('approved_by_admin_id');
            $table->unsignedBigInteger('claimed_by_id')->nullable()->after('fulfilled_by_id');
            $table->timestamp('request_date')->useCurrent()->after('claimed_by_id');
            $table->timestamp('admin_approval_date')->nullable()->after('request_date');
            $table->timestamp('fulfilled_date')->nullable()->after('admin_approval_date');
            $table->timestamp('claimed_date')->nullable()->after('fulfilled_date');
            $table->timestamp('fulfillment_date')->nullable()->after('claimed_date');
            $table->timestamp('claim_date')->nullable()->after('fulfillment_date');
            $table->date('approval_date')->nullable()->after('claim_date');
            $table->text('decline_reason')->nullable()->after('approval_date');
            $table->text('remarks')->nullable()->after('decline_reason');
            $table->text('admin_notes')->nullable()->after('remarks');
            $table->foreign('fulfilled_by_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('claimed_by_id')->references('id')->on('users')->onDelete('set null');
        });
    }
};
