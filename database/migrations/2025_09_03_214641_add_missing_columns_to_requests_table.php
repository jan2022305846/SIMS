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
            $table->text('purpose')->nullable()->after('quantity');
            $table->date('needed_date')->nullable()->after('purpose');
            $table->text('admin_notes')->nullable()->after('remarks');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('requests', function (Blueprint $table) {
            $table->dropColumn(['purpose', 'needed_date', 'admin_notes']);
        });
    }
};
