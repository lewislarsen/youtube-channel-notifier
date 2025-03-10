<?php

declare(strict_types=1);

use App\Console\Commands\ListVideosCommand;
use App\Models\Channel;
use App\Models\Video;
use Carbon\Carbon;

it('displays a list of videos', function (): void {
    $channel = Channel::factory()->create();
    $videos = Video::factory()->count(3)->for($channel)->create([
        'published_at' => now()->subDays(rand(1, 10)),
    ]);

    $this->artisan(ListVideosCommand::class)
        ->expectsTable([
            'Title', 'Creator', 'Published', 'URL',
        ], $videos->map(function (Video $video) {
            return [
                $video->title,
                $video->channel->name,
                Carbon::parse($video->published_at)->diffForHumans(),
                $video->getYoutubeUrl(),
            ];
        })->toArray());
});

it('displays an empty table when no videos exist', function (): void {
    $this->artisan(ListVideosCommand::class)
        ->expectsTable([
            'Title', 'Creator', 'Published', 'URL',
        ], []);
});

it('orders videos by most recently published', function (): void {
    $channel = Channel::factory()->create();
    $oldVideo = Video::factory()->for($channel)->create([
        'published_at' => now()->subDays(10),
    ]);
    $newVideo = Video::factory()->for($channel)->create([
        'published_at' => now(),
    ]);
    $middleVideo = Video::factory()->for($channel)->create([
        'published_at' => now()->subDays(5),
    ]);

    $this->artisan(ListVideosCommand::class)
        ->expectsTable([
            'Title', 'Creator', 'Published', 'URL',
        ], [
            [
                $newVideo->title,
                $newVideo->channel->name,
                Carbon::parse($newVideo->published_at)->diffForHumans(),
                $newVideo->getYoutubeUrl(),
            ],
            [
                $middleVideo->title,
                $middleVideo->channel->name,
                Carbon::parse($middleVideo->published_at)->diffForHumans(),
                $middleVideo->getYoutubeUrl(),
            ],
            [
                $oldVideo->title,
                $oldVideo->channel->name,
                Carbon::parse($oldVideo->published_at)->diffForHumans(),
                $oldVideo->getYoutubeUrl(),
            ],
        ]);
});

it('displays correct video information', function (): void {
    $channel = Channel::factory()->create();
    $video = Video::factory()->for($channel)->create();

    $this->artisan(ListVideosCommand::class)
        ->expectsTable([
            'Title', 'Creator', 'Published', 'URL',
        ], [
            [
                $video->title,
                $video->channel->name,
                Carbon::parse($video->published_at)->diffForHumans(),
                $video->getYoutubeUrl(),
            ],
        ]);
});
