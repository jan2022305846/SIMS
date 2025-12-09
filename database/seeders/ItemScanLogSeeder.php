<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\ItemScanLog;
use Carbon\Carbon;

class ItemScanLogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $scanLogs = [
            [
                'item_id' => 2, // Smart Bro Home Wifi Advanced D2
                'action' => 'inventory_check',
                'created_at' => Carbon::now()->subDays(2)->setTime(10, 30),
                'updated_at' => Carbon::now()->subDays(2)->setTime(10, 30),
            ],
            [
                'item_id' => 5, // Firefly Twinhead Emergency Lamp
                'action' => 'inventory_check',
                'created_at' => Carbon::now()->subDays(1)->setTime(14, 15),
                'updated_at' => Carbon::now()->subDays(1)->setTime(14, 15),
            ],
            [
                'item_id' => 4, // Rossetti 4.5L Electric Coffee Urn
                'action' => 'inventory_check',
                'created_at' => Carbon::now()->subHours(6)->setTime(9, 45),
                'updated_at' => Carbon::now()->subHours(6)->setTime(9, 45),
            ],
            [
                'item_id' => 2, // Smart Bro Home Wifi Advanced D2
                'action' => 'inventory_check',
                'created_at' => Carbon::now()->subHours(3)->setTime(11, 20),
                'updated_at' => Carbon::now()->subHours(3)->setTime(11, 20),
            ],
            [
                'item_id' => 5, // Firefly Twinhead Emergency Lamp
                'action' => 'inventory_check',
                'created_at' => Carbon::now()->subHours(1)->setTime(16, 10),
                'updated_at' => Carbon::now()->subHours(1)->setTime(16, 10),
            ],
        ];

        foreach ($scanLogs as $log) {
            ItemScanLog::create($log);
        }
    }
}
