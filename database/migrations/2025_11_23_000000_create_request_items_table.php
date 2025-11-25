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
        Schema::create('request_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('request_id')->constrained()->onDelete('cascade');
            $table->morphs('item'); // Creates item_id and item_type columns
            $table->integer('quantity')->default(1);
            $table->enum('status', ['available', 'reserved', 'unavailable'])->default('available');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['request_id', 'item_id', 'item_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('request_items');
    }
};