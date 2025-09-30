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
        Schema::create('non_consumables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('product_code')->unique(); // from item's qr_code or barcode
            $table->integer('quantity');
            $table->string('brand')->nullable();
            $table->integer('min_stock')->default(1);
            $table->integer('max_stock')->default(100);
            $table->integer('current_stock')->default(0);
            $table->foreignId('current_holder_id')->nullable()->constrained('users');
            $table->string('location');
            $table->string('condition')->default('Good');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('non_consumables');
    }
};
