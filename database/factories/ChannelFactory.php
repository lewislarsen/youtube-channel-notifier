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
     * Generate a realistic YouTube channel name.
     */
    protected function generateRealisticChannelName(): string
    {
        $channelNameFormats = [
            // Personal brand formats
            fn () => fake()->firstName().' '.fake()->lastName(),
            fn () => fake()->userName(),
            fn () => fake()->firstName().'TV',
            fn () => fake()->lastName().'Gaming',
            fn () => 'The'.fake()->lastName().'Family',

            // Topic-based formats
            fn () => fake()->word().' '.fake()->word().' Studio',
            fn () => fake()->word().'Tutorials',
            fn () => fake()->word().'Reviews',
            fn () => fake()->word().' '.fake()->word().' Official',
            fn () => 'The'.ucfirst(fake()->word()).'Channel',

            // Creative formats
            fn () => ucfirst(fake()->word()).ucfirst(fake()->word()),
            fn () => ucfirst(fake()->word()).fake()->numberBetween(1, 9999),
            fn () => 'The'.ucfirst(fake()->colorName()).ucfirst(fake()->word()),
            fn () => ucfirst(fake()->word()).'Hub',
            fn () => ucfirst(fake()->word()).'Nation',

            // Professional formats
            fn () => fake()->company().' Official',
            fn () => ucfirst(fake()->bs()),
            fn () => fake()->catchPhrase(),
        ];

        // Randomly select a format and generate a channel name
        $formatIndex = array_rand($channelNameFormats);
        $channelName = $channelNameFormats[$formatIndex]();

        // Sometimes add decorations like emojis or special characters
        if (fake()->boolean(20)) {
            $decorations = ['✓', '™', '®', '➤', '►', '★', '☆', '❤', '🎮', '🎬', '🎯', '🔥', '💯'];
            $channelName .= ' '.$decorations[array_rand($decorations)];
        }

        return $channelName;
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
}
