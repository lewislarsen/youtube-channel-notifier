<?php

namespace Database\Factories;

use App\Models\Channel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Class ChannelFactory
 *
 * This factory class is used to generate fake data for the Channel model. It defines
 * the default state for the model, which includes a fake channel name, a YouTube-like
 * channel ID, and an initial state for the last checked timestamp.
 *
 * @extends Factory<Channel>
 */
class ChannelFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed> The default state of the model.
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->name, // Generates a unique fake name for the channel
            'channel_id' => fake()->regexify('[A-Za-z0-9_-]{24}'), // Mimics YouTube channel ID format
            'last_checked_at' => null, // Initial state for new channels
            'muted_at' => null,
        ];
    }

    public function muted(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'muted_at' => now(),
            ];
        });
    }

    public function unmuted(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'muted_at' => null,
            ];
        });
    }
}
