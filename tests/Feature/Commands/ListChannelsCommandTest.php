<?php

use App\Console\Commands\ListChannelsCommand;
use App\Models\Channel;
use App\Models\Video;
use Carbon\Carbon;

it('displays a list of channels', function () {
    $channels = Channel::factory()->count(3)->create();

    $channels->each(fn ($channel) => [
        Video::factory()->count(rand(1, 5))->for($channel)->create(),
        $channel->update(['last_checked_at' => now()->subDays(rand(1, 10))]),
    ]);

    $this->artisan(ListChannelsCommand::class)
        ->expectsTable([
            'Name', 'Videos Stored', 'Last Video Grabbed', 'Channel URL', 'Muted',
        ], $channels->map(function (Channel $channel) {
            return [
                $channel->name,
                $channel->videos()->count(),
                Carbon::parse($channel->last_checked_at)->diffForHumans(),
                $channel->getChannelUrl(),
                $channel->isMuted() ? '✔' : '✘',
            ];
        })->toArray());
});

it('displays an empty table when no channels exist', function () {
    $this->artisan(ListChannelsCommand::class)
        ->expectsTable([
            'Name', 'Videos Stored', 'Last Video Grabbed', 'Channel URL', 'Muted',
        ], []);
});

it('shows muted status correctly for muted and unmuted channels', function () {
    $mutedChannel = Channel::factory()->muted()->create();
    $unmutedChannel = Channel::factory()->unmuted()->create();

    $this->artisan(ListChannelsCommand::class)
        ->expectsTable([
            'Name', 'Videos Stored', 'Last Video Grabbed', 'Channel URL', 'Muted',
        ], [
            [
                $mutedChannel->name,
                $mutedChannel->videos()->count(),
                Carbon::parse($mutedChannel->last_checked_at)->diffForHumans(),
                $mutedChannel->getChannelUrl(),
                '✔',
            ],
            [
                $unmutedChannel->name,
                $unmutedChannel->videos()->count(),
                Carbon::parse($unmutedChannel->last_checked_at)->diffForHumans(),
                $unmutedChannel->getChannelUrl(),
                '✘',
            ],
        ]);
});

it('orders channels by most recently created', function () {
    $oldChannel = Channel::factory()->create([
        'created_at' => now()->subDays(10),
        'last_checked_at' => now()->subDays(10),
    ]);
    $newChannel = Channel::factory()->create([
        'created_at' => now(),
        'last_checked_at' => now(),
    ]);
    $middleChannel = Channel::factory()->create([
        'created_at' => now()->subDays(5),
        'last_checked_at' => now()->subDays(5),
    ]);

    $this->artisan(ListChannelsCommand::class)
        ->expectsTable([
            'Name', 'Videos Stored', 'Last Video Grabbed', 'Channel URL', 'Muted',
        ], [
            [
                $newChannel->name,
                $newChannel->videos()->count(),
                Carbon::parse($newChannel->last_checked_at)->diffForHumans(),
                $newChannel->getChannelUrl(),
                $newChannel->isMuted() ? '✔' : '✘',
            ],
            [
                $middleChannel->name,
                $middleChannel->videos()->count(),
                Carbon::parse($middleChannel->last_checked_at)->diffForHumans(),
                $middleChannel->getChannelUrl(),
                $middleChannel->isMuted() ? '✔' : '✘',
            ],
            [
                $oldChannel->name,
                $oldChannel->videos()->count(),
                Carbon::parse($oldChannel->last_checked_at)->diffForHumans(),
                $oldChannel->getChannelUrl(),
                $oldChannel->isMuted() ? '✔' : '✘',
            ],
        ]);
});
