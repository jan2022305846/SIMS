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
            // Only add columns that don't already exist
            if (!Schema::hasColumn('items', 'barcode')) {
                $table->string('barcode')->nullable()->after('description');
            }
            if (!Schema::hasColumn('items', 'qr_code_data')) {
                $table->text('qr_code_data')->nullable()->after('barcode');
            }
            if (!Schema::hasColumn('items', 'brand')) {
                $table->string('brand')->nullable()->after('price');
            }
            if (!Schema::hasColumn('items', 'supplier')) {
                $table->string('supplier')->nullable()->after('brand');
            }
            if (!Schema::hasColumn('items', 'warranty_date')) {
                $table->date('warranty_date')->nullable()->after('supplier');
            }
            if (!Schema::hasColumn('items', 'minimum_stock')) {
                $table->integer('minimum_stock')->default(1)->after('warranty_date');
            }
            if (!Schema::hasColumn('items', 'maximum_stock')) {
                $table->integer('maximum_stock')->default(100)->after('minimum_stock');
            }
            if (!Schema::hasColumn('items', 'current_stock')) {
                $table->integer('current_stock')->default(0)->after('maximum_stock');
            }
            if (!Schema::hasColumn('items', 'unit_price')) {
                $table->decimal('unit_price', 10, 2)->default(0.00)->after('current_stock');
            }
            if (!Schema::hasColumn('items', 'total_value')) {
                $table->decimal('total_value', 12, 2)->default(0.00)->after('unit_price');
            }
            if (!Schema::hasColumn('items', 'current_holder_id')) {
                $table->foreignId('current_holder_id')->nullable()->constrained('users')->onDelete('set null')->after('total_value');
            }
            if (!Schema::hasColumn('items', 'assigned_at')) {
                $table->timestamp('assigned_at')->nullable()->after('current_holder_id');
            }
            if (!Schema::hasColumn('items', 'assignment_notes')) {
                $table->text('assignment_notes')->nullable()->after('assigned_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('items', function (Blueprint $table) {
            $table->dropForeign(['current_holder_id']);
            $table->dropColumn([
                'barcode',
                'qr_code_data', 
                'brand',
                'supplier',
                'warranty_date',
                'minimum_stock',
                'maximum_stock',
                'current_stock',
                'unit_price',
                'total_value',
                'current_holder_id',
                'assigned_at',
                'assignment_notes'
            ]);
        });
    }
};
