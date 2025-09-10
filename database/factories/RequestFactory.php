<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;
use App\Models\Item;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Request>
 */
class RequestFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::where('role', 'faculty')->inRandomOrder()->first()?->id ?? User::factory(['role' => 'faculty']),
            'item_id' => Item::inRandomOrder()->first()?->id ?? Item::factory(),
            'quantity' => fake()->numberBetween(1, 5),
            'status' => fake()->randomElement(['pending', 'approved', 'declined', 'returned']),
            'request_date' => fake()->date(),
            'approval_date' => fake()->optional()->date(),
            'return_date' => fake()->optional()->date(),
        ];
    }
}
