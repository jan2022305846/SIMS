<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Category;

class CategoryTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create or update categories with appropriate types
        $categories = [
            ['name' => 'Office Supplies', 'description' => 'Basic office supplies and stationery', 'type' => 'consumable'],
            ['name' => 'Electronics', 'description' => 'Electronic equipment and devices', 'type' => 'non-consumable'],
            ['name' => 'Furniture', 'description' => 'Office furniture and fixtures', 'type' => 'non-consumable'],
            ['name' => 'Cleaning Supplies', 'description' => 'Cleaning materials and chemicals', 'type' => 'consumable'],
            ['name' => 'IT Equipment', 'description' => 'Computers, printers, and IT hardware', 'type' => 'non-consumable'],
            ['name' => 'Medical Supplies', 'description' => 'Medical and first aid supplies', 'type' => 'consumable'],
            ['name' => 'Educational Materials', 'description' => 'Books, teaching aids, and educational resources', 'type' => 'non-consumable'],
            ['name' => 'Laboratory Equipment', 'description' => 'Scientific instruments and lab equipment', 'type' => 'non-consumable'],
            ['name' => 'Safety Equipment', 'description' => 'Safety gear and protective equipment', 'type' => 'non-consumable'],
            ['name' => 'Consumables', 'description' => 'General consumable items', 'type' => 'consumable'],
        ];

        foreach ($categories as $categoryData) {
            Category::updateOrCreate(
                ['name' => $categoryData['name']],
                $categoryData
            );
        }

        // Update any existing categories without a type to default 'non-consumable'
        Category::whereNull('type')->update(['type' => 'non-consumable']);
    }
}
