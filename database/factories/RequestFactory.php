<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;
use App\Models\Consumable;
use App\Models\NonConsumable;

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
        // Randomly choose between consumable and non_consumable
        $itemType = fake()->randomElement(['consumable', 'non_consumable']);
        
        $item = null;
        if ($itemType === 'consumable') {
            $item = Consumable::inRandomOrder()->first();
        } else {
            $item = NonConsumable::inRandomOrder()->first();
        }

        return [
            'user_id' => User::where('role', 'faculty')->inRandomOrder()->first()?->id ?? User::factory(['role' => 'faculty']),
            'item_id' => $item?->id ?? ($itemType === 'consumable' ? Consumable::factory() : NonConsumable::factory()),
            'item_type' => $itemType,
            'quantity' => fake()->numberBetween(1, 5),
            'status' => fake()->randomElement(['pending', 'approved', 'declined', 'returned']),
            'request_date' => fake()->date(),
            'approval_date' => fake()->optional()->date(),
            'return_date' => fake()->optional()->date(),
        ];
    }
}
