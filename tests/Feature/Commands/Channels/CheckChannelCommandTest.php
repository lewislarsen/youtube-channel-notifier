<?php

declare(strict_types=1);

use App\Actions\CheckForVideosAction;
use App\Actions\YouTube\ExtractYouTubeChannelId;
use App\Console\Commands\Channels\AddChannelCommand;
use App\Console\Commands\Channels\CheckChannelsCommand;
use App\Models\Channel;
use App\Models\Video;
use Illuminate\Support\Facades\Mail;
use Mockery\MockInterface;

beforeEach(function (): void {
    Channel::truncate();
    Video::truncate();
});

// CheckChannelsCommand tests
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

// AddChannelCommand tests
it('adds a new channel using URL and performs an initial video import', function (): void {
    Mail::fake();

    // Mock the ExtractYouTubeChannelId action
    $this->mock(ExtractYouTubeChannelId::class, function (MockInterface $mock): void {
        $mock->shouldReceive('execute')
            ->once()
            ->with('@test_channel', false)
            ->andReturn('UC_x5XG1OV2P6uZZ5FSM9Ttw');
    });

    $this->artisan(AddChannelCommand::class)
        ->expectsQuestion('Enter the channel name', 'Test Channel')
        ->expectsQuestion('Enter the channel URL or handle (e.g., https://www.youtube.com/@channelname or @channelname)', '@test_channel')
        ->expectsOutputToContain('Extracting channel ID from: @test_channel')
        ->expectsOutputToContain('Extracted channel ID: UC_x5XG1OV2P6uZZ5FSM9Ttw')
        ->expectsOutputToContain("Channel 'Test Channel' added successfully!")
        ->expectsOutputToContain("Running initial video import for 'Test Channel'...")
        ->expectsOutputToContain('Initial import completed successfully.');

    $channel = Channel::where('channel_id', 'UC_x5XG1OV2P6uZZ5FSM9Ttw')->first();
    expect($channel)->not->toBeNull();

    Mail::assertNothingSent();
});

it('falls back to manual entry when channel ID extraction fails', function (): void {
    Mail::fake();

    // Mock the ExtractYouTubeChannelId action to throw an exception
    $this->mock(ExtractYouTubeChannelId::class, function (MockInterface $mock): void {
        $mock->shouldReceive('execute')
            ->once()
            ->with('@invalid_channel', false)
            ->andThrow(new Exception('Failed to extract channel ID'));
    });

    $this->artisan(AddChannelCommand::class)
        ->expectsQuestion('Enter the channel name', 'Test Channel')
        ->expectsQuestion('Enter the channel URL or handle (e.g., https://www.youtube.com/@channelname or @channelname)', '@invalid_channel')
        ->expectsOutputToContain('Failed to automatically extract channel ID: Failed to extract channel ID')
        ->expectsOutputToContain('Falling back to manual channel ID entry.')
        ->expectsQuestion('Please enter the channel ID manually', 'UC_x5XG1OV2P6uZZ5FSM9Ttw')
        ->expectsOutputToContain("Channel 'Test Channel' added successfully!")
        ->expectsOutputToContain('Initial import completed successfully.');

    $channel = Channel::where('channel_id', 'UC_x5XG1OV2P6uZZ5FSM9Ttw')->first();
    expect($channel)->not->toBeNull();
});

it('does not add a channel if a channel with the same ID already exists', function (): void {
    Channel::factory()->create([
        'name' => 'Existing Channel',
        'channel_id' => 'UC_x5XG1OV2P6uZZ5FSM9Ttw',
    ]);

    // Mock the ExtractYouTubeChannelId action
    $this->mock(ExtractYouTubeChannelId::class, function (MockInterface $mock): void {
        $mock->shouldReceive('execute')
            ->once()
            ->with('@test_channel', false)
            ->andReturn('UC_x5XG1OV2P6uZZ5FSM9Ttw');
    });

    $this->artisan(AddChannelCommand::class)
        ->expectsQuestion('Enter the channel name', 'Test Channel')
        ->expectsQuestion('Enter the channel URL or handle (e.g., https://www.youtube.com/@channelname or @channelname)', '@test_channel')
        ->expectsOutputToContain('A channel with this ID already exists in the database.');

    expect(Channel::count())->toBe(1);
});

it('does not add a channel if manual ID entry is empty', function (): void {
    // Mock the ExtractYouTubeChannelId action to throw an exception
    $this->mock(ExtractYouTubeChannelId::class, function (MockInterface $mock): void {
        $mock->shouldReceive('execute')
            ->once()
            ->with('@invalid_channel', false)
            ->andThrow(new Exception('Failed to extract channel ID'));
    });

    $this->artisan(AddChannelCommand::class)
        ->expectsQuestion('Enter the channel name', 'Test Channel')
        ->expectsQuestion('Enter the channel URL or handle (e.g., https://www.youtube.com/@channelname or @channelname)', '@invalid_channel')
        ->expectsOutputToContain('Failed to automatically extract channel ID: Failed to extract channel ID')
        ->expectsQuestion('Please enter the channel ID manually', '')
        ->expectsOutputToContain('Channel ID is required.');

    expect(Channel::count())->toBe(0);
});
