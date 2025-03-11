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
        $titleFormats = [
            // How-to and tutorial formats
            fn () => 'How to '.fake()->sentence(3, true),
            fn () => 'Tutorial: '.ucfirst(fake()->word()).' for Beginners',
            fn () => 'Learn '.ucfirst(fake()->word()).' in '.fake()->numberBetween(5, 60).' Minutes',
            fn () => ucfirst(fake()->word()).' Masterclass: '.fake()->sentence(2, true),

            // Listicle formats
            fn () => fake()->numberBetween(3, 20).' '.ucfirst(fake()->words(3, true)).' You Need to See',
            fn () => 'Top '.fake()->numberBetween(5, 20).' '.ucfirst(fake()->words(2, true)).' of '.date('Y'),
            fn () => 'The BEST '.ucfirst(fake()->word()).' '.ucfirst(fake()->word()).' Ever!',

            // Review formats
            fn () => ucfirst(fake()->word()).' '.ucfirst(fake()->word()).' Review: Is It Worth It?',
            fn () => 'Honest Review: '.ucfirst(fake()->words(3, true)),
            fn () => 'I Tried '.ucfirst(fake()->words(2, true)).' For a Week and This Happened',

            // Vlog and personal content
            fn () => 'Day in the Life of a '.fake()->jobTitle(),
            fn () => 'I '.ucfirst(fake()->word()).' for '.fake()->numberBetween(10, 100).' Days Straight',
            fn () => 'My '.ucfirst(fake()->word()).' Routine '.date('Y'),

            // Reaction videos
            fn () => 'REACTING to '.ucfirst(fake()->words(3, true)),
            fn () => 'Reaction: '.ucfirst(fake()->words(3, true)),

            // Clickbait formats (but not too extreme)
            fn () => 'You Won\'t Believe What Happened When I '.fake()->sentence(3, true),
            fn () => 'This '.ucfirst(fake()->word()).' Changed Everything!',
            fn () => 'I Can\'t Believe '.ucfirst(fake()->words(4, true)).'...',

            // Gaming content
            fn () => ucfirst(fake()->word()).' Gameplay - Part '.fake()->numberBetween(1, 30),
            fn () => 'Let\'s Play '.ucfirst(fake()->word()).' - Episode '.fake()->numberBetween(1, 50),
        ];

        // Randomly select a format and generate a title
        $formatIndex = array_rand($titleFormats);
        $title = $titleFormats[$formatIndex]();

        // Sometimes add attention-grabbing elements
        if (fake()->boolean(40)) {
            $attention = ['(MUST WATCH)', '(SHOCKING)', '(NEW METHOD)', '[OFFICIAL]', '[PART '.fake()->numberBetween(1, 10).']'];
            $title .= ' '.$attention[array_rand($attention)];
        }

        return $title;
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
