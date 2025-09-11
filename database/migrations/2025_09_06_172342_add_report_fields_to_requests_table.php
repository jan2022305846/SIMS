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
            if (!Schema::hasColumn('requests', 'quantity_approved')) {
                $table->integer('quantity_approved')->nullable()->after('quantity');
            }
            if (!Schema::hasColumn('requests', 'processed_at')) {
                $table->timestamp('processed_at')->nullable()->after('claimed_date');
            }
            if (!Schema::hasColumn('requests', 'processed_by')) {
                $table->unsignedBigInteger('processed_by')->nullable()->after('processed_at');
                
                // Add foreign key constraint only if column was created
                $table->foreign('processed_by')->references('id')->on('users')->onDelete('set null');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('requests', function (Blueprint $table) {
            $table->dropForeign(['processed_by']);
            $table->dropColumn(['quantity_approved', 'processed_at', 'processed_by']);
        });
    }
};
