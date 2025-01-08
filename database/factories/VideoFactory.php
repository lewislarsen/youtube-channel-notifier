<?php

namespace Database\Factories;

use App\Models\Channel;
use App\Models\Video;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Class VideoFactory
 *
 * This factory class is used to generate fake data for the Video model. It defines
 * the default state for the model, which includes a fake YouTube video ID, title,
 * description, publication date, and an associated channel.
 *
 * @extends Factory<Video>
 */
class VideoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed> The default state of the model.
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
