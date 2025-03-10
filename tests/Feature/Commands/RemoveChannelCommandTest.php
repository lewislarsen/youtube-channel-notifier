<?php

use App\Console\Commands\RemoveChannelCommand;
use App\Models\Channel;
use App\Models\Video;

it('removes a channel and all data', function () {

    $channel = Channel::factory()->create();

    Video::factory()->count(3)->create([
        'channel_id' => $channel->id,
    ]);

    $this->artisan(RemoveChannelCommand::class)
        ->expectsQuestion('Enter the channel name', $channel->name)
        ->expectsConfirmation("Are you sure you want to remove the channel '{$channel->name}' and all related data?", 'yes')
        ->expectsOutputToContain("Channel '{$channel->name}' has been removed.");

    $this->assertDatabaseMissing('channels', [
        'name' => $channel->name,
    ]);

    $this->assertDatabaseMissing('videos', [
        'channel_id' => $channel->id,
    ]);
});

it('outputs a message if it cannot find a channel', function () {

    $this->artisan(RemoveChannelCommand::class)
        ->expectsQuestion('Enter the channel name', 'does-not-exist')
        ->expectsOutputToContain('A channel cannot be found with that name. Please run `php artisan channels:list`.');

    $this->assertDatabaseCount('channels', 0);
});

it('cancels the removal if the user does not confirm', function () {

    $channel = Channel::factory()->create();

    Video::factory()->count(3)->create([
        'channel_id' => $channel->id,
    ]);

    $this->artisan(RemoveChannelCommand::class)
        ->expectsQuestion('Enter the channel name', $channel->name)
        ->expectsConfirmation("Are you sure you want to remove the channel '{$channel->name}' and all related data?", 'no')
        ->expectsOutputToContain('Channel removal has been cancelled.');

    $this->assertDatabaseHas('channels', [
        'name' => $channel->name,
    ]);

    $this->assertDatabaseHas('videos', [
        'channel_id' => $channel->id,
    ]);
});
