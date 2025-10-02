<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Category;
use App\Models\Consumable;
use App\Models\NonConsumable;
use App\Models\Request;
use App\Models\Log;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Call the UserSeeder first
        $this->call([
            UserSeeder::class,
        ]);
        
        // Then create the rest of your test data
        User::factory()->count(3)->create();
        Category::factory(2)->create(); // Consumable, Non-Consumable
        Consumable::factory(8)->create();
        NonConsumable::factory(7)->create();
        Request::factory(10)->create();
        Log::factory(20)->create();
    }
}