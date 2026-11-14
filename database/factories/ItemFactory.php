<?php

namespace Database\Factories;

use App\Models\Item;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Item>
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
            'user_id' => User::factory(),
            'title' => fake()->words(3, true),
            'description' => fake()->sentence(),
            'quantity' => 1,
            'price_per_day' => 1.00,
        ];
    }

    public function ownedBy(User $owner): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $owner->id,
        ]);
    }
    public function randomPrice(): static
    {
        return $this->state(fn (array $attributes) => [
            'price_per_day' => fake()->randomFloat(2, 1, 100),
        ]);
    }
}
