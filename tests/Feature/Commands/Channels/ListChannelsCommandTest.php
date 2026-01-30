<?php

declare(strict_types=1);

use App\Console\Commands\Channels\ListChannelsCommand;
use App\Models\Channel;
use App\Models\Video;

it('displays a list of channels', function (): void {
    $channels = Channel::factory()->count(3)->create();

    $channels->each(fn ($channel) => [
        Video::factory()->count(rand(1, 5))->for($channel)->create(),
        $channel->update(['last_checked_at' => now()->subDays(rand(1, 10))]),
    ]);

    $this->artisan(ListChannelsCommand::class)
        ->expectsTable([
            'Name', 'Videos Stored', 'Last Video Grabbed', 'Last Notification', 'Channel URL', 'Muted', 'Note',
        ], $channels->map(function (Channel $channel) {
            $latestNotifiedVideo = $channel->videos()
                ->whereNotNull('notified_at')
                ->latest('notified_at')
                ->first();

            return [
                $channel->name,
                $channel->videos()->count(),
                $channel->last_checked_at?->setTimezone(config('app.user_timezone'))->diffForHumans() ?? '—',
                $latestNotifiedVideo?->notified_at?->setTimezone(config('app.user_timezone'))->diffForHumans() ?? '—',
                $channel->getChannelUrl(),
                $channel->isMuted() ? '✔' : '✘',
                $channel->note ?? '—',
            ];
        })->toArray());
});

it('displays an empty table when no channels exist', function (): void {
    $this->artisan(ListChannelsCommand::class)
        ->expectsTable([
            'Name', 'Videos Stored', 'Last Video Grabbed', 'Last Notification', 'Channel URL', 'Muted', 'Note',
        ], []);
});

it('shows muted status correctly for muted and unmuted channels', function (): void {
    $mutedChannel = Channel::factory()->muted()->create();
    $unmutedChannel = Channel::factory()->unmuted()->create();

    $this->artisan(ListChannelsCommand::class)
        ->expectsTable([
            'Name', 'Videos Stored', 'Last Video Grabbed', 'Last Notification', 'Channel URL', 'Muted', 'Note',
        ], [
            [
                $mutedChannel->name,
                $mutedChannel->videos()->count(),
                $mutedChannel->last_checked_at?->setTimezone(config('app.user_timezone'))->diffForHumans() ?? '—',
                '—',
                $mutedChannel->getChannelUrl(),
                '✔',
                $mutedChannel->note ?? '—',
            ],
            [
                $unmutedChannel->name,
                $unmutedChannel->videos()->count(),
                $unmutedChannel->last_checked_at?->setTimezone(config('app.user_timezone'))->diffForHumans() ?? '—',
                '—',
                $unmutedChannel->getChannelUrl(),
                '✘',
                $unmutedChannel->note ?? '—',
            ],
        ]);
});

it('orders channels by most recently created', function (): void {
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
            'Name', 'Videos Stored', 'Last Video Grabbed', 'Last Notification', 'Channel URL', 'Muted', 'Note',
        ], [
            [
                $newChannel->name,
                $newChannel->videos()->count(),
                $newChannel->last_checked_at?->setTimezone(config('app.user_timezone'))->diffForHumans() ?? '—',
                '—',
                $newChannel->getChannelUrl(),
                $newChannel->isMuted() ? '✔' : '✘',
                $newChannel->note ?? '—',
            ],
            [
                $middleChannel->name,
                $middleChannel->videos()->count(),
                $middleChannel->last_checked_at?->setTimezone(config('app.user_timezone'))->diffForHumans() ?? '—',
                '—',
                $middleChannel->getChannelUrl(),
                $middleChannel->isMuted() ? '✔' : '✘',
                $middleChannel->note ?? '—',
            ],
            [
                $oldChannel->name,
                $oldChannel->videos()->count(),
                $oldChannel->last_checked_at?->setTimezone(config('app.user_timezone'))->diffForHumans() ?? '—',
                '—',
                $oldChannel->getChannelUrl(),
                $oldChannel->isMuted() ? '✔' : '✘',
                $oldChannel->note ?? '—',
            ],
        ]);
});

it('displays last notification correctly when videos have notified_at timestamps', function (): void {
    $channel = Channel::factory()->create();

    Video::factory()->for($channel)->create([
        'notified_at' => now()->subDays(5),
    ]);
    Video::factory()->for($channel)->create([
        'notified_at' => now()->subDays(2), // This should be the latest
    ]);
    Video::factory()->for($channel)->create([
        'notified_at' => now()->subDays(7),
    ]);

    Video::factory()->for($channel)->create([
        'notified_at' => null,
    ]);

    $latestNotifiedVideo = $channel->videos()
        ->whereNotNull('notified_at')
        ->latest('notified_at')
        ->first();

    $this->artisan(ListChannelsCommand::class)
        ->expectsTable([
            'Name', 'Videos Stored', 'Last Video Grabbed', 'Last Notification', 'Channel URL', 'Muted', 'Note',
        ], [
            [
                $channel->name,
                $channel->videos()->count(),
                $channel->last_checked_at?->setTimezone(config('app.user_timezone'))->diffForHumans() ?? '—',
                $latestNotifiedVideo->notified_at?->setTimezone(config('app.user_timezone'))->diffForHumans() ?? '—',
                $channel->getChannelUrl(),
                $channel->isMuted() ? '✔' : '✘',
                $channel->note ?? '—',
            ],
        ]);
});

it('displays em dash when no videos have been notified', function (): void {
    $channel = Channel::factory()->create();

    Video::factory()->count(3)->for($channel)->create([
        'notified_at' => null,
    ]);

    $this->artisan(ListChannelsCommand::class)
        ->expectsTable([
            'Name', 'Videos Stored', 'Last Video Grabbed', 'Last Notification', 'Channel URL', 'Muted', 'Note',
        ], [
            [
                $channel->name,
                $channel->videos()->count(),
                $channel->last_checked_at?->setTimezone(config('app.user_timezone'))->diffForHumans() ?? '—',
                '—',
                $channel->getChannelUrl(),
                $channel->isMuted() ? '✔' : '✘',
                $channel->note ?? '—',
            ],
        ]);
});

it('displays note content when channel has a note', function (): void {
    $channel = Channel::factory()->create([
        'note' => 'This is a test note',
    ]);

    $this->artisan(ListChannelsCommand::class)
        ->expectsTable([
            'Name', 'Videos Stored', 'Last Video Grabbed', 'Last Notification', 'Channel URL', 'Muted', 'Note',
        ], [
            [
                $channel->name,
                $channel->videos()->count(),
                $channel->last_checked_at?->setTimezone(config('app.user_timezone'))->diffForHumans() ?? '—',
                '—',
                $channel->getChannelUrl(),
                $channel->isMuted() ? '✔' : '✘',
                'This is a test note',
            ],
        ]);

    $this->assertEquals('This is a test note', $channel->note);
});

it('displays em dash when channel has no note', function (): void {
    $channel = Channel::factory()->create([
        'note' => null,
    ]);

    $this->artisan(ListChannelsCommand::class)
        ->expectsTable([
            'Name', 'Videos Stored', 'Last Video Grabbed', 'Last Notification', 'Channel URL', 'Muted', 'Note',
        ], [
            [
                $channel->name,
                $channel->videos()->count(),
                $channel->last_checked_at?->setTimezone(config('app.user_timezone'))->diffForHumans() ?? '—',
                '—',
                $channel->getChannelUrl(),
                $channel->isMuted() ? '✔' : '✘',
                '—',
            ],
        ]);

    $this->assertNull($channel->note);
});
