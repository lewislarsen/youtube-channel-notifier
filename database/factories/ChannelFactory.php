<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Channel>
 */
class ChannelFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->name, // You may replace `channelName()` with a more suitable method if unavailable
            'channel_id' => fake()->regexify('[A-Za-z0-9_-]{24}'), // Mimics YouTube channel ID format
            'last_checked_at' => null, // Initial state for new channels
        ];
    }
}
