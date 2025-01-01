<?php

namespace Database\Factories;

use App\Models\Channel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Video>
 */
class VideoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'video_id' => fake()->regexify('[A-Za-z0-9_-]{11}'), // Mimics YouTube video ID format
            'title' => fake()->sentence(),
            'description' => fake()->paragraph(),
            'published_at' => fake()->dateTimeBetween('-1 year', 'now'),
            'channel_id' => Channel::factory(), // Automatically associate a video with a new channel
        ];
    }
}
