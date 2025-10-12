<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Category;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Consumable>
 */
class ConsumableFactory extends Factory
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
            'product_code' => 'PC-' . strtoupper(fake()->unique()->randomLetter() . fake()->randomNumber(6)),
            'quantity' => fake()->numberBetween(1, 50),
            'unit' => fake()->randomElement(['pieces', 'reams', 'liters', 'boxes']),
            'brand' => fake()->company(),
            'min_stock' => fake()->numberBetween(1, 10),
            'max_stock' => fake()->numberBetween(50, 200),
        ];
    }
}