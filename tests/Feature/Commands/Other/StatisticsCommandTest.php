<?php

declare(strict_types=1);

use App\Console\Commands\Other\StatisticsCommand;
use App\Models\Channel;
use App\Models\Video;
use Illuminate\Support\Facades\Config;

it('displays complete statistics with all sections', function (): void {
    $mutedChannel = Channel::factory()->muted()->create([
        'name' => 'Muted Test Channel',
        'created_at' => now()->subDays(10),
        'muted_at' => now()->subDays(5),
    ]);

    $activeChannel = Channel::factory()->unmuted()->create([
        'name' => 'Active Test Channel',
        'created_at' => now()->subDays(1),
    ]);

    Video::factory()->count(3)->for($mutedChannel)->create([
        'created_at' => now()->subDays(7),
    ]);

    $latestVideo = Video::factory()->for($activeChannel)->create([
        'title' => 'Latest Test Video',
        'created_at' => now()->subHours(2),
    ]);

    Config::set('app.alert_emails', ['test@example.com', 'admin@example.com']);
    Config::set('app.discord_webhook_url', 'https://discord.com/webhook/test');
    Config::set('app.webhook_post_url', 'https://example.com/webhook');

    $this->artisan(StatisticsCommand::class)
        ->expectsOutputToContain('Fetching real-time statistics - no caching is active')
        ->expectsTable(
            ['Section', 'Information', 'Details'],
            [['📊 SYSTEM OVERVIEW', 'Monitored Channels', 2],
                ['', 'Total Videos Tracked', 4],
                ['', '', ''],
                ['📺 CHANNEL STATISTICS', 'Active Channels', 1],
                ['', 'Muted Channels', 1],
                ['', '', ''],
                ['🆕 LATEST CHANNEL', 'Channel Name', 'Active Test Channel'],
                ['', 'Channel URL', $activeChannel->getChannelUrl()],
                ['', 'Added on', $activeChannel->created_at->format('Y-m-d H:i:s').' ('.$activeChannel->created_at->diffForHumans().')'],
                ['', '', ''],
                ['🎬 LATEST VIDEO', 'Video Title', 'Latest Test Video'],
                ['', 'Video URL', $latestVideo->getYoutubeUrl()],
                ['', 'Channel', 'Active Test Channel'],
                ['', 'Added on', $latestVideo->created_at->format('Y-m-d H:i:s').' ('.$latestVideo->created_at->diffForHumans().')'],
                ['', '', ''],
                ['📧 NOTIFICATION SETTINGS', 'Email Notifications', 'Enabled'],
                ['', 'Discord Notifications', 'Enabled'],
                ['', 'POST Webhook Notification', 'Enabled'],
                ['', 'Total Active Methods', 3],
                ['', 'Email Recipients', 'test@example.com, admin@example.com'],
                ['', '', ''],
                ['🔕 MUTED CHANNELS', 'Since '.$mutedChannel->muted_at->format('Y-m-d H:i:s').' ('.$mutedChannel->muted_at->diffForHumans().')',
                    $mutedChannel->name.' ('.$mutedChannel->getChannelUrl().')'],
                ['', '', ''],
            ]
        );
});

it('displays statistics with no muted channels', function (): void {
    $activeChannel = Channel::factory()->unmuted()->create([
        'name' => 'Active Test Channel',
        'created_at' => now()->subDays(1),
    ]);

    $latestVideo = Video::factory()->for($activeChannel)->create([
        'title' => 'Latest Test Video',
        'created_at' => now()->subHours(2),
    ]);

    Config::set('app.alert_emails', ['test@example.com']);
    Config::set('app.discord_webhook_url', '');
    Config::set('app.webhook_post_url', '');

    // Execute command and verify output - should exclude muted channels section
    $this->artisan(StatisticsCommand::class)
        ->expectsOutputToContain('Fetching real-time statistics - no caching is active')
        ->expectsTable(
            ['Section', 'Information', 'Details'],
            [['📊 SYSTEM OVERVIEW', 'Monitored Channels', 1],
                ['', 'Total Videos Tracked', 1],
                ['', '', ''],
                ['📺 CHANNEL STATISTICS', 'Active Channels', 1],
                ['', 'Muted Channels', 0],
                ['', '', ''],
                ['🆕 LATEST CHANNEL', 'Channel Name', 'Active Test Channel'],
                ['', 'Channel URL', $activeChannel->getChannelUrl()],
                ['', 'Added on', $activeChannel->created_at->format('Y-m-d H:i:s').' ('.$activeChannel->created_at->diffForHumans().')'],
                ['', '', ''],
                ['🎬 LATEST VIDEO', 'Video Title', 'Latest Test Video'],
                ['', 'Video URL', $latestVideo->getYoutubeUrl()],
                ['', 'Channel', 'Active Test Channel'],
                ['', 'Added on', $latestVideo->created_at->format('Y-m-d H:i:s').' ('.$latestVideo->created_at->diffForHumans().')'],
                ['', '', ''],
                ['📧 NOTIFICATION SETTINGS', 'Email Notifications', 'Enabled'],
                ['', 'Discord Notifications', 'Disabled'],
                ['', 'POST Webhook Notification', 'Disabled'],
                ['', 'Total Active Methods', 1],
                ['', 'Email Recipients', 'test@example.com'],
                ['', '', ''],
            ]
        );
});

it('displays statistics with no videos tracked', function (): void {
    $channel = Channel::factory()->unmuted()->create([
        'name' => 'Empty Channel',
        'created_at' => now()->subDays(1),
    ]);

    Config::set('app.alert_emails', []);
    Config::set('app.discord_webhook_url', '');
    Config::set('app.webhook_post_url', '');

    $this->artisan(StatisticsCommand::class)
        ->expectsOutputToContain('Fetching real-time statistics - no caching is active')
        ->expectsTable(
            ['Section', 'Information', 'Details'],
            [['📊 SYSTEM OVERVIEW', 'Monitored Channels', 1],
                ['', 'Total Videos Tracked', 0],
                ['', '', ''],
                ['📺 CHANNEL STATISTICS', 'Active Channels', 1],
                ['', 'Muted Channels', 0],
                ['', '', ''],
                ['🆕 LATEST CHANNEL', 'Channel Name', 'Empty Channel'],
                ['', 'Channel URL', $channel->getChannelUrl()],
                ['', 'Added on', $channel->created_at->format('Y-m-d H:i:s').' ('.$channel->created_at->diffForHumans().')'],
                ['', '', ''],
                ['📧 NOTIFICATION SETTINGS', 'Email Notifications', 'Disabled'],
                ['', 'Discord Notifications', 'Disabled'],
                ['', 'POST Webhook Notification', 'Disabled'],
                ['', 'Total Active Methods', 0],
                ['', '', ''],
            ]
        );
});

it('displays a message with no channels', function (): void {
    Config::set('app.alert_emails', []);
    Config::set('app.discord_webhook_url', '');

    $this->artisan(StatisticsCommand::class)
        ->expectsOutputToContain('No channels found. Please add channels using `php artisan channels:add` to get statistics.');

    $this->assertDatabaseMissing('channels', []);
    $this->assertDatabaseMissing('videos', []);
});
