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
        Schema::table('offices', function (Blueprint $table) {
            $table->dropForeign(['office_head_id']);
            $table->dropColumn(['code', 'description', 'office_head_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('offices', function (Blueprint $table) {
            $table->string('code')->nullable()->after('name');
            $table->text('description')->nullable()->after('code');
            $table->unsignedBigInteger('office_head_id')->nullable()->after('location');
            $table->foreign('office_head_id')->references('id')->on('users')->onDelete('set null');
        });
    }
};
