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
        Schema::create('stock_reservations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('request_item_id')->constrained()->onDelete('cascade');
            $table->morphs('item'); // item_id and item_type
            $table->integer('quantity_reserved');
            $table->timestamp('reserved_until')->nullable();
            $table->enum('status', ['active', 'expired', 'fulfilled', 'cancelled'])->default('active');
            $table->timestamps();

            $table->index(['item_id', 'item_type', 'status']);
            $table->index(['reserved_until']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_reservations');
    }
};
