<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Category;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Item>
 */
class ItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'category_id' => Category::inRandomOrder()->first()?->id ?? Category::factory(),
            'name' => fake()->words(2, true),
            'description' => fake()->sentence(),
            'quantity' => fake()->numberBetween(1, 50),
            'price' => fake()->numberBetween(100, 50000),
            'unit' => fake()->randomElement(['pcs', 'reams', 'liters']),
            'location' => fake()->randomElement(['IT Building', 'Admin Office', 'Faculty Office']),
            'condition' => fake()->randomElement(['Good', 'Needs Repair']),
            'qr_code' => fake()->uuid(),
            'expiry_date' => fake()->optional()->dateTimeBetween('now', '+1 year'),
        ];
    }
}
