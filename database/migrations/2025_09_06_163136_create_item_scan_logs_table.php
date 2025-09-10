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
        Schema::create('item_scan_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->constrained('items')->onDelete('cascade');
            $table->timestamp('scanned_at')->default(now()); // When the QR code was scanned
            $table->string('location')->nullable(); // Where the scan occurred
            $table->string('scanner_type')->default('admin'); // Who can scan (admin only as per ERD)
            $table->json('scan_data')->nullable(); // Additional scan metadata
            $table->string('ip_address')->nullable(); // IP address of scanner
            $table->text('user_agent')->nullable(); // Browser/device info
            $table->timestamps();
            
            $table->index(['item_id', 'scanned_at']);
            $table->index(['scanned_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('item_scan_logs');
    }
};
