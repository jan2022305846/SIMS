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
        Schema::table('items', function (Blueprint $table) {
            $table->foreignId('current_holder_id')->nullable()->after('total_value')
                  ->constrained('users')->onDelete('set null');
            $table->timestamp('assigned_at')->nullable()->after('current_holder_id');
            $table->text('assignment_notes')->nullable()->after('assigned_at');
            
            $table->index(['current_holder_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('items', function (Blueprint $table) {
            $table->dropForeign(['current_holder_id']);
            $table->dropIndex(['current_holder_id']);
            $table->dropColumn(['current_holder_id', 'assigned_at', 'assignment_notes']);
        });
    }
};
