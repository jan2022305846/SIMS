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
        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('categories')->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->integer('quantity');
            $table->integer('price')->nullable();
            $table->string('unit')->nullable(); // optional
            $table->string('location');
            $table->string('condition')->default('Good');
            $table->string('qr_code')->unique();
            $table->date('expiry_date')->nullable(); // only for consumables
            $table->timestamps();
            $table->softDeletes(); // for Laravel's soft delete
        });        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('items');
    }
};
