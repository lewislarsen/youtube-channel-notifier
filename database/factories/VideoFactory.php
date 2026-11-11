<?php

namespace Database\Factories;

use App\Models\Channel;
use App\Models\Video;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Class VideoFactory
 *
 * This factory class is used to generate fake data for the Video model. It defines
 * the default state for the model, which includes a realistic YouTube video ID, title,
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
            'title' => $this->generateRealisticVideoTitle(),
            'description' => $this->generateRealisticVideoDescription(),
            'published_at' => fake()->dateTimeBetween('-1 year', 'now'),
            'channel_id' => Channel::factory(), // Automatically associate a video with a new channel
        ];
    }

    /**
     * Generate a realistic YouTube video title.
     */
    protected function generateRealisticVideoTitle(): string
    {
        $howToTopics = [
            'organize my camera files',
            'plan a week of meals',
            'edit a travel video',
            'keep a small workspace tidy',
            'back up photos without overthinking it',
            'make better coffee at home',
            'track my spending each month',
            'get started with Blender',
        ];

        $weeklongExperiments = [
            'cooking every meal at home',
            'running before work',
            'using a paper planner',
            'cycling instead of driving',
            'shooting a roll of film',
            'learning to sew',
            'making espresso at home',
            'taking a break from social media',
        ];

        $projects = [
            'a quiet home server from spare parts',
            'a standing desk from plywood',
            'my first mechanical keyboard',
            'a balcony garden for under £50',
            'a simple home recording setup',
            'a better bike storage rack',
        ];

        $topics = [
            'film photography',
            'home espresso',
            'trail running',
            'meal planning',
            'mechanical keyboards',
            'digital illustration',
            'home networking',
            'weekend train travel',
        ];

        $titles = [
            fn (): string => 'How I '.fake()->randomElement($howToTopics),
            fn (): string => 'A week of '.fake()->randomElement($weeklongExperiments).': what I learned',
            fn (): string => 'Building '.fake()->randomElement($projects),
            fn (): string => 'A practical guide to '.fake()->randomElement($topics),
            fn (): string => 'What I learned after a year of '.fake()->randomElement($topics),
            fn (): string => 'My current '.fake()->randomElement(['camera kit', 'desk setup', 'home server', 'everyday bike'])
                .' (and what I would change)',
            fn (): string => 'A weekend in '.fake()->city().' without a car',
            fn (): string => 'I tried '.fake()->randomElement($weeklongExperiments).' for 30 days',
        ];

        return fake()->randomElement($titles)();
    }

    /**
     * Generate a realistic YouTube video description.
     */
    protected function generateRealisticVideoDescription(): string
    {
        $description = '';

        // Opening greeting/intro
        $intros = [
            'Hey everyone! ',
            'What\'s up guys? ',
            'Welcome back to the channel! ',
            'Thanks for watching! ',
            'Hello beautiful people! ',
        ];
        $description .= $intros[array_rand($intros)];

        // Main description content
        $description .= fake()->paragraph(2)."\n\n";

        // Add timestamps (50% chance)
        if (fake()->boolean(50)) {
            $description .= "TIMESTAMPS:\n";
            $minutes = 0;
            for ($i = 0; $i < fake()->numberBetween(3, 8); $i++) {
                $minutes += fake()->numberBetween(1, 5);
                $seconds = fake()->numberBetween(0, 59);
                $timestamp = sprintf('%02d:%02d', $minutes, $seconds);
                $description .= $timestamp.' - '.ucfirst(fake()->words(fake()->numberBetween(2, 5), true))."\n";
            }
            $description .= "\n";
        }

        // Add call to action
        $ctas = [
            'Don\'t forget to like and subscribe for more content like this!',
            'If you enjoyed this video, hit that like button and subscribe!',
            'Make sure to hit the notification bell to stay updated!',
            'Let me know in the comments what you want to see next!',
            'Thanks for watching! Subscribe for weekly uploads.',
        ];
        $description .= $ctas[array_rand($ctas)]."\n\n";

        // Add social media links (40% chance)
        if (fake()->boolean(40)) {
            $description .= "FOLLOW ME:\n";
            $description .= 'Instagram: '.'@'.fake()->userName()."\n";
            $description .= 'Twitter: '.'@'.fake()->userName()."\n";
            $description .= 'TikTok: '.'@'.fake()->userName()."\n\n";
        }

        // Add hashtags
        $description .= '#'.ucfirst(fake()->word()).' ';
        $description .= '#'.ucfirst(fake()->word()).ucfirst(fake()->word()).' ';
        $description .= '#'.ucfirst(fake()->word())."\n";

        return $description;
    }
}
