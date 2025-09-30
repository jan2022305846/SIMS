<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ItemsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get category IDs
        $officeSuppliesId = \App\Models\Category::where('name', 'Office Supplies')->first()->id;
        $electronicsId = \App\Models\Category::where('name', 'Electronics')->first()->id;
        $cleaningSuppliesId = \App\Models\Category::where('name', 'Cleaning Supplies')->first()->id;

        $items = [
            // Office Supplies - Consumables
            ['category_id' => $officeSuppliesId, 'name' => 'F4 Bond paper', 'product_code' => '8993242597150', 'type' => 'consumable', 'quantity' => 100, 'min_stock' => 10, 'max_stock' => 500, 'current_stock' => 50, 'brand' => 'PaperOne'],
            ['category_id' => $officeSuppliesId, 'name' => 'A4 Bond paper', 'product_code' => '8993242593619', 'type' => 'consumable', 'quantity' => 100, 'min_stock' => 10, 'max_stock' => 500, 'current_stock' => 75, 'brand' => 'PaperOne'],
            ['category_id' => $officeSuppliesId, 'name' => 'Qto Bond paper', 'product_code' => '8993242597167', 'type' => 'consumable', 'quantity' => 100, 'min_stock' => 10, 'max_stock' => 500, 'current_stock' => 30, 'brand' => 'PaperOne'],
            ['category_id' => $officeSuppliesId, 'name' => 'Logbook Valiant 500 pages', 'product_code' => '4800556000075', 'type' => 'consumable', 'quantity' => 50, 'min_stock' => 5, 'max_stock' => 100, 'current_stock' => 20, 'brand' => 'Valiant'],
            ['category_id' => $officeSuppliesId, 'name' => 'Flexstick 0.5 Black Ballpen', 'product_code' => '8935001880141', 'type' => 'consumable', 'quantity' => 200, 'min_stock' => 20, 'max_stock' => 1000, 'current_stock' => 150, 'brand' => 'Flexstick'],
            ['category_id' => $officeSuppliesId, 'name' => 'Flexstick 0.5 Blue Ballpen', 'product_code' => '8935001878742', 'type' => 'consumable', 'quantity' => 200, 'min_stock' => 20, 'max_stock' => 1000, 'current_stock' => 120, 'brand' => 'Flexstick'],
            ['category_id' => $officeSuppliesId, 'name' => 'Youmei 0.5 Blue Ballpen', 'product_code' => '6932808780027', 'type' => 'consumable', 'quantity' => 150, 'min_stock' => 15, 'max_stock' => 750, 'current_stock' => 80, 'brand' => 'Youmei'],
            ['category_id' => $officeSuppliesId, 'name' => 'Dong-A Mygel 0.5 Red Gel Ink Pen', 'product_code' => '8802203083673', 'type' => 'consumable', 'quantity' => 100, 'min_stock' => 10, 'max_stock' => 500, 'current_stock' => 60, 'brand' => 'Dong-A'],
            ['category_id' => $officeSuppliesId, 'name' => 'Dong-A Mygel 0.7 Blue Gel Ink Pen', 'product_code' => '8802203521380', 'type' => 'consumable', 'quantity' => 100, 'min_stock' => 10, 'max_stock' => 500, 'current_stock' => 45, 'brand' => 'Dong-A'],
            ['category_id' => $officeSuppliesId, 'name' => 'Pilot Broad Black Marker', 'product_code' => '4902505088179', 'type' => 'consumable', 'quantity' => 50, 'min_stock' => 5, 'max_stock' => 200, 'current_stock' => 25, 'brand' => 'Pilot'],
            ['category_id' => $officeSuppliesId, 'name' => 'Pilot Fine Black Marker', 'product_code' => '4902505088094', 'type' => 'consumable', 'quantity' => 50, 'min_stock' => 5, 'max_stock' => 200, 'current_stock' => 30, 'brand' => 'Pilot'],
            ['category_id' => $officeSuppliesId, 'name' => 'Flex Marker Red Whiteboard Marker', 'product_code' => '8935001870340', 'type' => 'consumable', 'quantity' => 40, 'min_stock' => 4, 'max_stock' => 150, 'current_stock' => 20, 'brand' => 'Flex'],
            ['category_id' => $officeSuppliesId, 'name' => 'Hi White Chalk', 'product_code' => '8851934601007', 'type' => 'consumable', 'quantity' => 100, 'min_stock' => 10, 'max_stock' => 500, 'current_stock' => 70, 'brand' => 'Hi'],
            ['category_id' => $officeSuppliesId, 'name' => 'TM Big 50mm Paperclip', 'product_code' => '4806530360395', 'type' => 'consumable', 'quantity' => 500, 'min_stock' => 50, 'max_stock' => 2000, 'current_stock' => 300, 'brand' => 'TM'],
            ['category_id' => $officeSuppliesId, 'name' => 'TM 70mm Paper Fastener', 'product_code' => '4806530360432', 'type' => 'consumable', 'quantity' => 200, 'min_stock' => 20, 'max_stock' => 1000, 'current_stock' => 120, 'brand' => 'TM'],
            ['category_id' => $officeSuppliesId, 'name' => 'TM 8M Correction Tape', 'product_code' => '4806530368438', 'type' => 'consumable', 'quantity' => 100, 'min_stock' => 10, 'max_stock' => 300, 'current_stock' => 60, 'brand' => 'TM'],
            ['category_id' => $officeSuppliesId, 'name' => 'Elmer\'s 240 grams Multi-Purpose Glue', 'product_code' => '026000003797', 'type' => 'consumable', 'quantity' => 50, 'min_stock' => 5, 'max_stock' => 150, 'current_stock' => 25, 'brand' => 'Elmer\'s'],
            ['category_id' => $officeSuppliesId, 'name' => 'Elmer\'s 130 grams Multi-Purpose Glue', 'product_code' => '026000223720', 'type' => 'consumable', 'quantity' => 75, 'min_stock' => 8, 'max_stock' => 200, 'current_stock' => 40, 'brand' => 'Elmer\'s'],

            // Electronics - Consumables
            ['category_id' => $electronicsId, 'name' => 'Epson 664 Cyan Ink', 'product_code' => '8885007020242', 'type' => 'consumable', 'quantity' => 20, 'min_stock' => 2, 'max_stock' => 50, 'current_stock' => 10, 'brand' => 'Epson'],
            ['category_id' => $electronicsId, 'name' => 'Epson 664 Yellow Ink', 'product_code' => '8885007020266', 'type' => 'consumable', 'quantity' => 20, 'min_stock' => 2, 'max_stock' => 50, 'current_stock' => 8, 'brand' => 'Epson'],
            ['category_id' => $electronicsId, 'name' => 'Epson 003 Cyan Ink', 'product_code' => '8885007027890', 'type' => 'consumable', 'quantity' => 15, 'min_stock' => 2, 'max_stock' => 40, 'current_stock' => 12, 'brand' => 'Epson'],
            ['category_id' => $electronicsId, 'name' => 'Epson 003 Magenta Ink', 'product_code' => '8885007027913', 'type' => 'consumable', 'quantity' => 15, 'min_stock' => 2, 'max_stock' => 40, 'current_stock' => 9, 'brand' => 'Epson'],
            ['category_id' => $electronicsId, 'name' => 'Epson 003 Black Ink', 'product_code' => '8885007027876', 'type' => 'consumable', 'quantity' => 15, 'min_stock' => 2, 'max_stock' => 40, 'current_stock' => 11, 'brand' => 'Epson'],
            ['category_id' => $electronicsId, 'name' => 'Epson 003 Yellow Ink', 'product_code' => '8885007027937', 'type' => 'consumable', 'quantity' => 15, 'min_stock' => 2, 'max_stock' => 40, 'current_stock' => 7, 'brand' => 'Epson'],
            ['category_id' => $electronicsId, 'name' => 'Brother 6000BK Black Ink', 'product_code' => '4977766748124', 'type' => 'consumable', 'quantity' => 10, 'min_stock' => 1, 'max_stock' => 30, 'current_stock' => 5, 'brand' => 'Brother'],
            ['category_id' => $electronicsId, 'name' => 'Brother BT5000M Magenta Ink', 'product_code' => '4977766748148', 'type' => 'consumable', 'quantity' => 10, 'min_stock' => 1, 'max_stock' => 30, 'current_stock' => 6, 'brand' => 'Brother'],
            ['category_id' => $electronicsId, 'name' => 'Brother BT5000C Cyan Ink', 'product_code' => '4977766748131', 'type' => 'consumable', 'quantity' => 10, 'min_stock' => 1, 'max_stock' => 30, 'current_stock' => 4, 'brand' => 'Brother'],
            ['category_id' => $electronicsId, 'name' => 'Brother BT5000Y Yellow Ink', 'product_code' => '4977766748155', 'type' => 'consumable', 'quantity' => 10, 'min_stock' => 1, 'max_stock' => 30, 'current_stock' => 3, 'brand' => 'Brother'],

            // Electronics - Consumables (continued)
            ['category_id' => $electronicsId, 'name' => 'FireFly 18W Led Bulb', 'product_code' => '4806036500189', 'type' => 'consumable', 'quantity' => 50, 'min_stock' => 5, 'max_stock' => 200, 'current_stock' => 25, 'brand' => 'FireFly'],

            // Electronics - Non-Consumables
            ['category_id' => $electronicsId, 'name' => 'Smart Bro Home Wifi Advanced D2', 'product_code' => 'D2SGS221417271', 'type' => 'non_consumable', 'current_holder_id' => null, 'location' => 'Supply Office', 'condition' => 'New'],
            ['category_id' => $electronicsId, 'name' => 'Smart Bro Home Wifi Advanced D2', 'product_code' => 'D2SGS221408331', 'type' => 'non_consumable', 'current_holder_id' => null, 'location' => 'Supply Office', 'condition' => 'New'],
            ['category_id' => $electronicsId, 'name' => 'Smart Bro Home Wifi Advanced D2', 'product_code' => 'D2SGS221420198', 'type' => 'non_consumable', 'current_holder_id' => null, 'location' => 'Supply Office', 'condition' => 'New'],
            ['category_id' => $electronicsId, 'name' => 'Rossetti 4.5L Electric Coffee Urn', 'product_code' => '4800315001145', 'type' => 'non_consumable', 'current_holder_id' => null, 'location' => 'Supply Office', 'condition' => 'New'],
            ['category_id' => $electronicsId, 'name' => 'Firefly Twinhead Emergency Lamp', 'product_code' => '2050380222866', 'type' => 'non_consumable', 'current_holder_id' => null, 'location' => 'Supply Office', 'condition' => 'New'],
            ['category_id' => $cleaningSuppliesId, 'name' => 'Albatross Lemon Deodorizer', 'product_code' => '4800067600382', 'type' => 'consumable', 'quantity' => 50, 'min_stock' => 5, 'max_stock' => 150, 'current_stock' => 25, 'brand' => 'Albatross'],
            ['category_id' => $cleaningSuppliesId, 'name' => 'Albatross Jasmine Deodorizer', 'product_code' => '4800067600344', 'type' => 'consumable', 'quantity' => 50, 'min_stock' => 5, 'max_stock' => 150, 'current_stock' => 30, 'brand' => 'Albatross'],
            ['category_id' => $cleaningSuppliesId, 'name' => 'Albatross Sweet Marmalade Deodorizer', 'product_code' => '4800067608692', 'type' => 'consumable', 'quantity' => 50, 'min_stock' => 5, 'max_stock' => 150, 'current_stock' => 20, 'brand' => 'Albatross'],
            ['category_id' => $cleaningSuppliesId, 'name' => 'Lysol Fresh Blossom 681ml Disinfectant Spray', 'product_code' => '4801002062883', 'type' => 'consumable', 'quantity' => 30, 'min_stock' => 3, 'max_stock' => 100, 'current_stock' => 15, 'brand' => 'Lysol'],
            ['category_id' => $cleaningSuppliesId, 'name' => 'Lysol Early Morning Breeze 681ml Disinfectant Spray', 'product_code' => '9556111408279', 'type' => 'consumable', 'quantity' => 30, 'min_stock' => 3, 'max_stock' => 100, 'current_stock' => 18, 'brand' => 'Lysol'],
            ['category_id' => $cleaningSuppliesId, 'name' => 'Solbac Lavender Mist 400g Disinfectant Spray', 'product_code' => '4806513690051', 'type' => 'consumable', 'quantity' => 25, 'min_stock' => 3, 'max_stock' => 80, 'current_stock' => 12, 'brand' => 'Solbac'],
            ['category_id' => $cleaningSuppliesId, 'name' => 'Domex Classic 900ml Toilet Cleaner', 'product_code' => '8934868115717', 'type' => 'consumable', 'quantity' => 40, 'min_stock' => 4, 'max_stock' => 120, 'current_stock' => 22, 'brand' => 'Domex'],
            ['category_id' => $cleaningSuppliesId, 'name' => 'Domex Pink Power 900ml Toilet Cleaner', 'product_code' => '8934868115724', 'type' => 'consumable', 'quantity' => 40, 'min_stock' => 4, 'max_stock' => 120, 'current_stock' => 19, 'brand' => 'Domex'],
            ['category_id' => $cleaningSuppliesId, 'name' => 'Surf Cherry Blossom 120g Bar Soap', 'product_code' => '4800888190970', 'type' => 'consumable', 'quantity' => 100, 'min_stock' => 10, 'max_stock' => 300, 'current_stock' => 65, 'brand' => 'Surf'],
        ];

        foreach ($items as $item) {
            if ($item['type'] === 'consumable') {
                \App\Models\Consumable::firstOrCreate(
                    ['product_code' => $item['product_code']],
                    [
                        'category_id' => $item['category_id'],
                        'name' => $item['name'],
                        'description' => $item['name'],
                        'quantity' => $item['quantity'],
                        'brand' => $item['brand'],
                        'min_stock' => $item['min_stock'],
                        'max_stock' => $item['max_stock'],
                        'current_stock' => $item['current_stock'],
                    ]
                );
            } else {
                \App\Models\NonConsumable::firstOrCreate(
                    ['product_code' => $item['product_code']],
                    [
                        'category_id' => $item['category_id'],
                        'name' => $item['name'],
                        'description' => $item['name'],
                        'current_holder_id' => $item['current_holder_id'],
                        'location' => $item['location'],
                        'condition' => $item['condition'],
                        'current_stock' => 1, // Non-consumables typically have stock of 1
                    ]
                );
            }
        }
    }
}
