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
        Schema::create('request_acknowledgments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('request_id')->constrained('requests')->onDelete('cascade');
            $table->foreignId('acknowledged_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('witnessed_by')->nullable()->constrained('users')->onDelete('set null');
            
            // Digital signature data
            $table->text('signature_data')->nullable(); // Base64 encoded signature
            $table->string('signature_type')->default('digital'); // digital, manual, photo
            
            // Acknowledgment details
            $table->timestamp('acknowledged_at');
            $table->text('acknowledgment_notes')->nullable();
            $table->json('items_received')->nullable(); // JSON array of received items with quantities
            
            // Photo evidence
            $table->string('photo_path')->nullable();
            $table->string('photo_original_name')->nullable();
            
            // Receipt information
            $table->string('receipt_number')->unique();
            $table->text('receipt_data')->nullable(); // JSON data for receipt generation
            
            // Location and device info
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->json('location_data')->nullable(); // GPS coordinates if available
            
            // Verification
            $table->string('verification_hash')->nullable(); // Hash for integrity verification
            $table->boolean('is_verified')->default(true);
            $table->timestamp('verified_at')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index('request_id');
            $table->index('acknowledged_by');
            $table->index('acknowledged_at');
            $table->index('receipt_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('request_acknowledgments');
    }
};
