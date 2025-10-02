<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Category;
use App\Models\Consumable;
use App\Models\NonConsumable;
use App\Models\Request;
use App\Models\User;

class TestDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Categories
        $categories = [
            ['name' => 'Office Supplies', 'description' => 'Pens, papers, folders, etc.'],
            ['name' => 'Electronics', 'description' => 'Computers, printers, cables'],
            ['name' => 'Furniture', 'description' => 'Chairs, tables, cabinets'],
            ['name' => 'Cleaning Supplies', 'description' => 'Soap, sanitizers, tissues'],
        ];

        foreach ($categories as $category) {
            Category::firstOrCreate(['name' => $category['name']], $category);
        }

        // Create Consumable Items
        $consumables = [
            [
                'name' => 'Ballpoint Pen (Blue)',
                'description' => 'Standard blue ink ballpoint pen',
                'category_id' => 1,
                'product_code' => 'PEN-001',
                'quantity' => 50,
                'unit' => 'pieces',
                'brand' => 'Pilot',
                'min_stock' => 10,
                'max_stock' => 100,
            ],
            [
                'name' => 'A4 Bond Paper',
                'description' => '500 sheets white bond paper',
                'category_id' => 1,
                'product_code' => 'PAPER-001',
                'quantity' => 25,
                'unit' => 'reams',
                'brand' => 'Paperline',
                'min_stock' => 5,
                'max_stock' => 50,
            ],
            [
                'name' => 'Hand Sanitizer',
                'description' => '500ml alcohol-based hand sanitizer',
                'category_id' => 4,
                'product_code' => 'SANITIZER-001',
                'quantity' => 5,
                'unit' => 'bottles',
                'brand' => 'SafeGuard',
                'min_stock' => 10,
                'max_stock' => 20,
            ],
        ];

        foreach ($consumables as $item) {
            Consumable::firstOrCreate(['name' => $item['name']], $item);
        }

        // Create Non-Consumable Items
        $nonConsumables = [
            [
                'name' => 'Wireless Mouse',
                'description' => 'Wireless optical mouse',
                'category_id' => 2,
                'product_code' => 'MOUSE-001',
                'quantity' => 8,
                'unit' => 'pieces',
                'brand' => 'Logitech',
                'min_stock' => 3,
                'max_stock' => 15,
                'location' => 'IT Storage',
                'condition' => 'Good',
            ],
            [
                'name' => 'Office Chair',
                'description' => 'Ergonomic office chair with back support',
                'category_id' => 3,
                'product_code' => 'CHAIR-001',
                'quantity' => 2,
                'unit' => 'pieces',
                'brand' => 'Ergoflex',
                'min_stock' => 1,
                'max_stock' => 5,
                'location' => 'Furniture Storage',
                'condition' => 'Good',
            ],
        ];

        foreach ($nonConsumables as $item) {
            NonConsumable::firstOrCreate(['name' => $item['name']], $item);
        }

        // Create some sample requests
        $faculty = User::where('role', 'faculty')->first();
        if ($faculty) {
            $requests = [
                [
                    'user_id' => $faculty->id,
                    'item_id' => 1,
                    'quantity' => 10,
                    'purpose' => 'For classroom use',
                    'needed_date' => now()->addDays(7),
                    'status' => 'pending',
                ],
                [
                    'user_id' => $faculty->id,
                    'item_id' => 2,
                    'quantity' => 2,
                    'purpose' => 'For student handouts',
                    'needed_date' => now()->addDays(3),
                    'status' => 'approved',
                    'admin_notes' => 'Approved for educational use',
                ],
            ];

            foreach ($requests as $request) {
                Request::firstOrCreate([
                    'user_id' => $request['user_id'],
                    'item_id' => $request['item_id'],
                    'status' => $request['status']
                ], $request);
            }
        }
    }
}
