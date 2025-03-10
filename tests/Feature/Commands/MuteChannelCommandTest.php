<?php

declare(strict_types=1);

use App\Console\Commands\MuteChannelCommand;
use App\Models\Channel;

it('can mute a channel', function (): void {
    $channel = Channel::factory()->unmuted()->create();

    $this->artisan(MuteChannelCommand::class, ['name' => $channel->name])
        ->expectsOutputToContain("The channel '{$channel->name}' has been muted. You will no longer receive video notifications.");

    $channel = $channel->refresh();

    $this->assertTrue($channel->isMuted());
});

it('can mute a channel via interactive prompt', function (): void {
    $channel = Channel::factory()->unmuted()->create();

    $this->artisan(MuteChannelCommand::class)
        ->expectsQuestion('Enter the channel name', $channel->name)
        ->expectsOutputToContain("The channel '{$channel->name}' has been muted. You will no longer receive video notifications.");

    $channel = $channel->refresh();

    $this->assertTrue($channel->isMuted());
});

it('can unmute a channel', function (): void {
    $channel = Channel::factory()->muted()->create();

    $this->artisan(MuteChannelCommand::class, ['name' => $channel->name])
        ->expectsOutputToContain("The channel '{$channel->name}' has been un-muted. You will start receiving notifications again.");

    $channel = $channel->refresh();

    $this->assertFalse($channel->isMuted());
});

it('can unmute a channel via interactive prompt', function (): void {
    $channel = Channel::factory()->muted()->create();

    $this->artisan(MuteChannelCommand::class)
        ->expectsQuestion('Enter the channel name', $channel->name)
        ->expectsOutputToContain("The channel '{$channel->name}' has been un-muted. You will start receiving notifications again.");

    $channel = $channel->refresh();

    $this->assertFalse($channel->isMuted());
});

it('outputs a message if it cannot find a channel via parameter', function (): void {
    $this->artisan(MuteChannelCommand::class, ['name' => 'does-not-exist'])
        ->expectsOutputToContain('A channel cannot be found with that name. Please run `php artisan channels:list`.');

    $this->assertDatabaseMissing('channels', [
        'name' => 'does-not-exist',
    ]);
});

it('outputs a message if it cannot find a channel via interactive prompt', function (): void {
    $this->artisan(MuteChannelCommand::class)
        ->expectsQuestion('Enter the channel name', 'does-not-exist')
        ->expectsOutputToContain('A channel cannot be found with that name. Please run `php artisan channels:list`.');

    $this->assertDatabaseMissing('channels', [
        'name' => 'does-not-exist',
    ]);
});
