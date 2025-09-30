<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('requests', function (Blueprint $table) {
            // Add office_id column
            $table->unsignedBigInteger('office_id')->nullable()->after('user_id');

            // Create foreign key constraint
            $table->foreign('office_id')->references('id')->on('offices')->onDelete('set null');
        });

        // Populate office_id from user's office
        DB::statement('UPDATE requests SET office_id = (SELECT office_id FROM users WHERE users.id = requests.user_id)');

        Schema::table('requests', function (Blueprint $table) {
            // Drop the department column
            $table->dropColumn('department');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('requests', function (Blueprint $table) {
            // Add back department column
            $table->string('department')->nullable()->index()->after('claimed_date');
        });

        // Populate department from office name
        DB::statement('UPDATE requests SET department = (SELECT name FROM offices WHERE offices.id = requests.office_id)');

        Schema::table('requests', function (Blueprint $table) {
            // Drop foreign key and column
            $table->dropForeign(['office_id']);
            $table->dropColumn('office_id');
        });
    }
};
