<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Office;
use App\Models\Consumable;
use App\Models\OfficeItemLimit;

class OfficeItemLimitsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $offices = Office::all();
        $consumables = Consumable::all();

        foreach ($offices as $office) {
            foreach ($consumables as $consumable) {
                OfficeItemLimit::firstOrCreate(
                    [
                        'office_id' => $office->id,
                        'item_type' => 'consumable',
                        'item_id' => $consumable->id,
                    ],
                    [
                        'max_quantity' => 0, // Default to unlimited
                    ]
                );
            }
        }

        $this->command->info('Office item limits seeded successfully for ' . $offices->count() . ' offices and ' . $consumables->count() . ' consumables.');
    }
}
