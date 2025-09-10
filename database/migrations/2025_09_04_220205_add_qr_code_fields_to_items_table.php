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
            $table->date('warranty_date')->nullable()->after('supplier');
            $table->integer('maximum_stock')->default(100)->after('minimum_stock');
            $table->integer('current_stock')->default(0)->after('maximum_stock');
            $table->decimal('unit_price', 10, 2)->default(0)->after('current_stock');
            $table->decimal('total_value', 12, 2)->default(0)->after('unit_price');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('items', function (Blueprint $table) {
            $table->dropColumn([
                'warranty_date',
                'maximum_stock',
                'current_stock',
                'unit_price',
                'total_value'
            ]);
        });
    }
};
