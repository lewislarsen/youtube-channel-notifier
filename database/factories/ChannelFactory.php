<?php

namespace Database\Factories;

use App\Models\Channel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Class ChannelFactory
 *
 * This factory class is used to generate fake data for the Channel model. It defines
 * the default state for the model, which includes a realistic YouTube channel name,
 * a YouTube-like channel ID, and an initial state for the last checked timestamp.
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
            'name' => $this->generateRealisticChannelName(),
            'channel_id' => fake()->regexify('[A-Za-z0-9_-]{24}'), // Mimics YouTube channel ID format
            'last_checked_at' => null, // Initial state for new channels
            'muted_at' => null,
            'note' => fake()->optional(0.3)->sentence(), // Optional note with 30% chance of being set
        ];
    }

    /**
     * Mark the channel as muted.
     */
    public function muted(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'muted_at' => now(),
            ];
        });
    }

    /**
     * Mark the channel as unmuted.
     */
    public function unmuted(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'muted_at' => null,
            ];
        });
    }

    /**
     * Generate a realistic YouTube channel name.
     */
    protected function generateRealisticChannelName(): string
    {
        $styles = [
            fn (): string => fake()->firstName() . ' ' . fake()->lastName(),
            fn (): string => fake()->firstName() . ' ' . fake()->randomElement(['Makes', 'Builds', 'Cooks', 'Rides', 'Creates']),
            fn (): string => fake()->firstName() . ' on ' . fake()->randomElement(['Film', 'Foot', 'Food', 'Games', 'the Road']),
            fn (): string => 'The ' . fake()->randomElement(['Weekend Workshop', 'Sunday Kitchen', 'Camera Roll', 'Open Bench', 'Everyday Outdoors', 'Small Space Garden', 'Home Studio', 'Field Notes']),
            fn (): string => ucfirst(fake()->userName()),
        ];

        return fake()->randomElement($styles)();
    }
}
