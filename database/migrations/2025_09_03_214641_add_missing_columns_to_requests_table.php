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
            // Check if columns don't exist before adding them
            if (!Schema::hasColumn('requests', 'purpose')) {
                $table->text('purpose')->nullable()->after('quantity');
            }
            if (!Schema::hasColumn('requests', 'needed_date')) {
                $table->date('needed_date')->nullable()->after('purpose');
            }
            if (!Schema::hasColumn('requests', 'admin_notes')) {
                $table->text('admin_notes')->nullable()->after('remarks');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('requests', function (Blueprint $table) {
            // Check if columns exist before dropping them
            if (Schema::hasColumn('requests', 'admin_notes')) {
                $table->dropColumn('admin_notes');
            }
            if (Schema::hasColumn('requests', 'needed_date')) {
                $table->dropColumn('needed_date');
            }
            if (Schema::hasColumn('requests', 'purpose')) {
                $table->dropColumn('purpose');
            }
        });
    }
};
