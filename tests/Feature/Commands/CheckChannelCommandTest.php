<?php

use App\Actions\CheckForVideosAction;
use App\Console\Commands\AddChannelCommand;
use App\Console\Commands\CheckChannelsCommand;
use App\Models\Channel;
use App\Models\Video;
use Illuminate\Support\Facades\Mail;

beforeEach(function () {
    Channel::truncate();
    Video::truncate();
});

it('outputs a message when no channels are found', function () {
    $this->artisan(CheckChannelsCommand::class)
        ->expectsOutput('No channels found in the database.')
        ->assertExitCode(0);
});

it('handles CheckForVideosAction execution for each channel', function () {
    $action = Mockery::mock(CheckForVideosAction::class);
    app()->instance(CheckForVideosAction::class, $action);

    $channel1 = Channel::factory()->create([
        'name' => 'Channel One',
        'channel_id' => 'UC_x5XG1OV2P6uZZ5FSM9Ttw',
    ]);

    $channel2 = Channel::factory()->create([
        'name' => 'Channel Two',
        'channel_id' => 'UC_x6YG2P6uZZ5FSM9Ttw',
    ]);

    $action->shouldReceive('execute')->with(Mockery::on(function ($channel) use ($channel1) {
        return $channel->is($channel1);
    }))->once();

    $action->shouldReceive('execute')->with(Mockery::on(function ($channel) use ($channel2) {
        return $channel->is($channel2);
    }))->once();

    $this->artisan(CheckChannelsCommand::class)
        ->expectsOutput('Checking channels for new videos...')
        ->expectsOutput('Checking channel: Channel One (UC_x5XG1OV2P6uZZ5FSM9Ttw)')
        ->expectsOutput('Checking channel: Channel Two (UC_x6YG2P6uZZ5FSM9Ttw)')
        ->expectsOutput('Channel check completed.')
        ->assertExitCode(0);
});

it('adds a new channel and performs an initial video import', function () {
    Mail::fake();

    // Mock user input for channel name and ID
    $this->artisan(AddChannelCommand::class)
        ->expectsQuestion('Enter the channel name', 'Test Channel')
        ->expectsQuestion('Enter the channel ID', 'UC_x5XG1OV2P6uZZ5FSM9Ttw')
        ->expectsOutput("Channel 'Test Channel' added successfully!")
        ->expectsOutput("Running initial video import for 'Test Channel'...")
        ->expectsOutput('Initial import completed successfully.')
        ->assertExitCode(0);

    $channel = Channel::where('channel_id', 'UC_x5XG1OV2P6uZZ5FSM9Ttw')->first();
    expect($channel)->not->toBeNull();

    Mail::assertNothingSent();
});

it('does not add a channel if a channel with the same ID already exists', function () {
    Channel::factory()->create([
        'name' => 'Existing Channel',
        'channel_id' => 'UC_x5XG1OV2P6uZZ5FSM9Ttw',
    ]);

    $this->artisan(AddChannelCommand::class)
        ->expectsQuestion('Enter the channel name', 'Test Channel')
        ->expectsQuestion('Enter the channel ID', 'UC_x5XG1OV2P6uZZ5FSM9Ttw')
        ->expectsOutput('A channel with this ID already exists in the database.')
        ->assertExitCode(1);

    expect(Channel::count())->toBe(1);
});
