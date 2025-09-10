<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Category;
use App\Models\Item;
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

        // Create Items
        $items = [
            [
                'name' => 'Ballpoint Pen (Blue)',
                'description' => 'Standard blue ink ballpoint pen',
                'category_id' => 1,
                'quantity' => 50,
                'unit' => 'pieces',
                'price' => 15.00,
                'location' => 'Storage Room A',
                'condition' => 'Good',
                'qr_code' => 'PEN-001',
                'brand' => 'Pilot',
                'supplier' => 'Office Depot',
                'minimum_stock' => 10,
            ],
            [
                'name' => 'A4 Bond Paper',
                'description' => '500 sheets white bond paper',
                'category_id' => 1,
                'quantity' => 25,
                'unit' => 'reams',
                'price' => 250.00,
                'location' => 'Storage Room A',
                'condition' => 'Good',
                'qr_code' => 'PAPER-001',
                'brand' => 'Paperline',
                'supplier' => 'National Bookstore',
                'minimum_stock' => 5,
            ],
            [
                'name' => 'Wireless Mouse',
                'description' => 'Wireless optical mouse',
                'category_id' => 2,
                'quantity' => 8,
                'unit' => 'pieces',
                'price' => 850.00,
                'location' => 'IT Storage',
                'condition' => 'Good',
                'qr_code' => 'MOUSE-001',
                'brand' => 'Logitech',
                'supplier' => 'PC Express',
                'minimum_stock' => 3,
            ],
            [
                'name' => 'Office Chair',
                'description' => 'Ergonomic office chair with back support',
                'category_id' => 3,
                'quantity' => 2,
                'unit' => 'pieces',
                'price' => 5500.00,
                'location' => 'Furniture Storage',
                'condition' => 'Good',
                'qr_code' => 'CHAIR-001',
                'brand' => 'Ergoflex',
                'supplier' => 'Furniture Plus',
                'minimum_stock' => 1,
            ],
            [
                'name' => 'Hand Sanitizer',
                'description' => '500ml alcohol-based hand sanitizer',
                'category_id' => 4,
                'quantity' => 5,
                'unit' => 'bottles',
                'price' => 120.00,
                'location' => 'Medical Storage',
                'condition' => 'Good',
                'qr_code' => 'SANITIZER-001',
                'brand' => 'SafeGuard',
                'supplier' => 'Mercury Drug',
                'minimum_stock' => 10,
                'expiry_date' => now()->addMonths(18),
            ],
        ];

        foreach ($items as $item) {
            Item::firstOrCreate(['name' => $item['name']], $item);
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
