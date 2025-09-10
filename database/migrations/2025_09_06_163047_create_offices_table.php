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
        Schema::create('offices', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Department/Office name
            $table->string('code')->unique(); // Office code (e.g., 'IT', 'BSIT', 'SUPPLY')
            $table->text('description')->nullable();
            $table->string('location')->nullable(); // Office location
            $table->foreignId('office_head_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            
            $table->index(['name']);
            $table->index(['code']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('offices');
    }
};
