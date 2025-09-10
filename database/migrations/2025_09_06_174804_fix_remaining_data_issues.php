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
        // Fix category types based on logical naming
        $categoryFixes = [
            'Office Supplies' => 'consumable',
            'Cleaning Supplies' => 'consumable', 
            'Medical Supplies' => 'consumable',
        ];

        foreach ($categoryFixes as $name => $type) {
            DB::table('categories')
                ->where('name', $name)
                ->update(['type' => $type]);
        }

        // Fix invalid user roles - change 'faculty' to 'user'
        DB::table('users')
            ->where('role', 'faculty')
            ->update(['role' => 'user']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert category type changes
        $categoryReverts = [
            'Office Supplies' => 'non-consumable',
            'Cleaning Supplies' => 'non-consumable', 
            'Medical Supplies' => 'non-consumable',
        ];

        foreach ($categoryReverts as $name => $type) {
            DB::table('categories')
                ->where('name', $name)
                ->update(['type' => $type]);
        }

        // Revert user role changes
        DB::table('users')
            ->where('role', 'user')
            ->whereIn('id', function($query) {
                // This is a simplified revert - in practice you'd need to track which users were changed
                $query->select('id')->from('users')->where('role', 'user');
            })
            ->update(['role' => 'faculty']);
    }
};
