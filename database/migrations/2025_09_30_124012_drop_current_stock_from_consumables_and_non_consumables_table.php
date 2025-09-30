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
        Schema::table('consumables', function (Blueprint $table) {
            $table->dropColumn('current_stock');
        });

        Schema::table('non_consumables', function (Blueprint $table) {
            $table->dropColumn('current_stock');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('consumables', function (Blueprint $table) {
            $table->integer('current_stock')->default(0)->after('max_stock');
        });

        Schema::table('non_consumables', function (Blueprint $table) {
            $table->integer('current_stock')->default(0)->after('max_stock');
        });
    }
};
