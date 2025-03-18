<?php

declare(strict_types=1);

use App\Actions\CheckForVideosAction;
use App\Console\Commands\Channels\CheckChannelsCommand;
use App\Models\Channel;
use App\Models\Video;

beforeEach(function (): void {
    Channel::truncate();
    Video::truncate();
});

it('outputs a message when no channels are found', function (): void {
    $this->artisan(CheckChannelsCommand::class)
        ->expectsOutputToContain('No channels found in the database.')
        ->assertExitCode(0);
});

it('handles CheckForVideosAction execution for each channel', function (): void {
    $mock = Mockery::mock(CheckForVideosAction::class);
    app()->instance(CheckForVideosAction::class, $mock);

    $channel1 = Channel::factory()->create([
        'name' => 'Channel One',
        'channel_id' => 'UC_x5XG1OV2P6uZZ5FSM9Ttw',
    ]);

    $channel2 = Channel::factory()->create([
        'name' => 'Channel Two',
        'channel_id' => 'UC_x6YG2P6uZZ5FSM9Ttw',
    ]);

    $mock->shouldReceive('execute')->with(Mockery::on(function ($channel) use ($channel1) {
        return $channel->is($channel1);
    }))->once();

    $mock->shouldReceive('execute')->with(Mockery::on(function ($channel) use ($channel2) {
        return $channel->is($channel2);
    }))->once();

    $this->artisan(CheckChannelsCommand::class)
        ->expectsOutputToContain('Checking channels for new videos...')
        ->expectsOutputToContain('Checking channel: Channel One (UC_x5XG1OV2P6uZZ5FSM9Ttw)')
        ->expectsOutputToContain('Checking channel: Channel Two (UC_x6YG2P6uZZ5FSM9Ttw)')
        ->expectsOutputToContain('Channel check completed.')
        ->assertExitCode(0);
});
