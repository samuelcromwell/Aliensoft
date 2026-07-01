<?php

namespace Database\Factories;

use App\Models\Event;
use App\Models\TicketTier;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TicketTier>
 */
class TicketTierFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'event_id' => Event::factory(),
            'name' => fake()->unique()->words(2, true),
            'price' => fake()->randomFloat(2, 0, 500),
            'quantity' => fake()->numberBetween(1, 1000),
            'sales_channels' => null,
            'is_published' => false,
            'is_active' => true,
        ];
    }
}
