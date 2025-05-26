<?php

declare(strict_types=1);

use App\Console\Commands\Videos\ListVideosCommand;
use App\Models\Channel;
use App\Models\Video;
use Carbon\Carbon;

it('displays a list of videos', function (): void {
    $channel = Channel::factory()->create();
    $videos = Video::factory()->count(3)->for($channel)->create([
        'published_at' => now()->subDays(random_int(1, 10)),
    ]);

    $this->artisan(ListVideosCommand::class)
        ->expectsTable([
            'Title', 'Creator', 'Published', 'Notified', 'URL',
        ], $videos->map(function (Video $video) {
            return [
                $video->title,
                $video->channel->name,
                Carbon::parse($video->published_at)->diffForHumans(),
                $video->notified_at ? Carbon::parse($video->notified_at)->diffForHumans() : '—',
                $video->getYoutubeUrl(),
            ];
        })->toArray());
});

it('displays an empty table when no videos exist', function (): void {
    $this->artisan(ListVideosCommand::class)
        ->expectsTable([
            'Title', 'Creator', 'Published', 'Notified', 'URL',
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
            'Title', 'Creator', 'Published', 'Notified', 'URL',
        ], [
            [
                $newVideo->title,
                $newVideo->channel->name,
                Carbon::parse($newVideo->published_at)->diffForHumans(),
                $newVideo->notified_at ? Carbon::parse($newVideo->notified_at)->diffForHumans() : '—',
                $newVideo->getYoutubeUrl(),
            ],
            [
                $middleVideo->title,
                $middleVideo->channel->name,
                Carbon::parse($middleVideo->published_at)->diffForHumans(),
                $middleVideo->notified_at ? Carbon::parse($middleVideo->notified_at)->diffForHumans() : '—',
                $middleVideo->getYoutubeUrl(),
            ],
            [
                $oldVideo->title,
                $oldVideo->channel->name,
                Carbon::parse($oldVideo->published_at)->diffForHumans(),
                $oldVideo->notified_at ? Carbon::parse($oldVideo->notified_at)->diffForHumans() : '—',
                $oldVideo->getYoutubeUrl(),
            ],
        ]);
});

it('displays correct video information', function (): void {
    $channel = Channel::factory()->create();
    $video = Video::factory()->for($channel)->create();

    $this->artisan(ListVideosCommand::class)
        ->expectsTable([
            'Title', 'Creator', 'Published', 'Notified', 'URL',
        ], [
            [
                $video->title,
                $video->channel->name,
                Carbon::parse($video->published_at)->diffForHumans(),
                $video->notified_at ? Carbon::parse($video->notified_at)->diffForHumans() : '—',
                $video->getYoutubeUrl(),
            ],
        ]);
});

it('only displays notified videos when --notified flag is used', function (): void {
    $channel = Channel::factory()->create();

    $notifiedVideo = Video::factory()->for($channel)->create([
        'published_at' => now()->subDays(1),
        'notified_at' => now()->subHours(2),
    ]);

    $unnotifiedVideo = Video::factory()->for($channel)->create([
        'published_at' => now()->subDays(2),
        'notified_at' => null,
    ]);

    $anotherNotifiedVideo = Video::factory()->for($channel)->create([
        'published_at' => now()->subDays(3),
        'notified_at' => now()->subDays(1),
    ]);

    $this->artisan(ListVideosCommand::class, ['--notified' => true])
        ->expectsTable([
            'Title', 'Creator', 'Published', 'Notified', 'URL',
        ], [
            [
                $notifiedVideo->title,
                $notifiedVideo->channel->name,
                Carbon::parse($notifiedVideo->published_at)->diffForHumans(),
                Carbon::parse($notifiedVideo->notified_at)->diffForHumans(),
                $notifiedVideo->getYoutubeUrl(),
            ],
            [
                $anotherNotifiedVideo->title,
                $anotherNotifiedVideo->channel->name,
                Carbon::parse($anotherNotifiedVideo->published_at)->diffForHumans(),
                Carbon::parse($anotherNotifiedVideo->notified_at)->diffForHumans(),
                $anotherNotifiedVideo->getYoutubeUrl(),
            ],
        ]);
});

it('displays empty table when no notified videos exist', function (): void {
    $channel = Channel::factory()->create();

    Video::factory()->count(2)->for($channel)->create([
        'notified_at' => null,
    ]);

    $this->artisan(ListVideosCommand::class, ['--notified' => true])
        ->expectsTable([
            'Title', 'Creator', 'Published', 'Notified', 'URL',
        ], []);
});
