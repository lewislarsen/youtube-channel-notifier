<?php

declare(strict_types=1);

use App\Actions\YouTube\ExtractYouTubeChannelId;
use App\Console\Commands\Channels\AddChannelCommand;
use App\Models\Channel;
use App\Models\Video;
use Illuminate\Support\Facades\Mail;
use Mockery\MockInterface;

beforeEach(function (): void {
    Channel::truncate();
    Video::truncate();
});

it('adds a new channel using URL and performs an initial video import', function (): void {
    Mail::fake();

    $this->mock(ExtractYouTubeChannelId::class, function (MockInterface $mock): void {
        $mock->shouldReceive('execute')
            ->once()
            ->with('@test_channel', false)
            ->andReturn('UC_x5XG1OV2P6uZZ5FSM9Ttw');
    });

    $this->artisan(AddChannelCommand::class)
        ->expectsQuestion('Channel label?', 'Test Channel')
        ->expectsQuestion('Channel URL/@handle?', '@test_channel')
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

    $this->mock(ExtractYouTubeChannelId::class, function (MockInterface $mock): void {
        $mock->shouldReceive('execute')
            ->once()
            ->with('@invalid_channel', false)
            ->andThrow(new Exception('Failed to extract channel ID'));
    });

    $this->artisan(AddChannelCommand::class)
        ->expectsQuestion('Channel label?', 'Test Channel')
        ->expectsQuestion('Channel URL/@handle?', '@invalid_channel')
        ->expectsOutputToContain('Failed to automatically extract channel ID: Failed to extract channel ID')
        ->expectsOutputToContain('Falling back to manual channel ID entry.')
        ->expectsQuestion('Channel ID?', 'UC_x5XG1OV2P6uZZ5FSM9Ttw')
        ->expectsOutputToContain("Channel 'Test Channel' added successfully!")
        ->expectsOutputToContain("Running initial video import for 'Test Channel'...")
        ->expectsOutputToContain('Initial import completed successfully.');

    $channel = Channel::where('channel_id', 'UC_x5XG1OV2P6uZZ5FSM9Ttw')->first();
    expect($channel)->not->toBeNull();

    Mail::assertNothingSent();
});

it('does not add a channel if a channel with the same ID already exists', function (): void {
    Channel::factory()->create([
        'name' => 'Existing Channel',
        'channel_id' => 'UC_x5XG1OV2P6uZZ5FSM9Ttw',
    ]);

    $this->mock(ExtractYouTubeChannelId::class, function (MockInterface $mock): void {
        $mock->shouldReceive('execute')
            ->once()
            ->with('@test_channel', false)
            ->andReturn('UC_x5XG1OV2P6uZZ5FSM9Ttw');
    });

    $this->artisan(AddChannelCommand::class)
        ->expectsQuestion('Channel label?', 'Test Channel')
        ->expectsQuestion('Channel URL/@handle?', '@test_channel')
        ->expectsOutputToContain('A channel with this ID already exists in the database.');

    expect(Channel::count())->toBe(1);
});

it('does not add a channel if manual ID entry is empty', function (): void {
    $this->mock(ExtractYouTubeChannelId::class, function (MockInterface $mock): void {
        $mock->shouldReceive('execute')
            ->once()
            ->with('@invalid_channel', false)
            ->andThrow(new Exception('Failed to extract channel ID'));
    });

    $this->artisan(AddChannelCommand::class)
        ->expectsQuestion('Channel label?', 'Test Channel')
        ->expectsQuestion('Channel URL/@handle?', '@invalid_channel')
        ->expectsOutputToContain('Failed to automatically extract channel ID: Failed to extract channel ID')
        ->expectsQuestion('Channel ID?', '');

    expect(Channel::count())->toBe(0);
});

it('adds @ to a channel handle that does not have it', function (): void {
    Mail::fake();

    $this->mock(ExtractYouTubeChannelId::class, function (MockInterface $mock): void {
        $mock->shouldReceive('execute')
            ->once()
            ->with('@testchannel', false)
            ->andReturn('UC_x5XG1OV2P6uZZ5FSM9Ttw');
    });

    $this->artisan(AddChannelCommand::class)
        ->expectsQuestion('Channel label?', 'Test Channel')
        ->expectsQuestion('Channel URL/@handle?', 'testchannel')
        ->expectsOutputToContain('Extracting channel ID from: @testchannel')
        ->expectsOutputToContain('Extracted channel ID: UC_x5XG1OV2P6uZZ5FSM9Ttw')
        ->expectsOutputToContain("Channel 'Test Channel' added successfully!");

    $channel = Channel::where('channel_id', 'UC_x5XG1OV2P6uZZ5FSM9Ttw')->first();
    expect($channel)->not->toBeNull();
});

it('adds @ to a channel handle in a YouTube URL', function (): void {
    Mail::fake();

    $this->mock(ExtractYouTubeChannelId::class, function (MockInterface $mock): void {
        $mock->shouldReceive('execute')
            ->once()
            ->with('https://www.youtube.com/@testchannel', false)
            ->andReturn('UC_x5XG1OV2P6uZZ5FSM9Ttw');
    });

    $this->artisan(AddChannelCommand::class)
        ->expectsQuestion('Channel label?', 'Test Channel')
        ->expectsQuestion('Channel URL/@handle?', 'https://www.youtube.com/testchannel')
        ->expectsOutputToContain('Extracting channel ID from: https://www.youtube.com/@testchannel')
        ->expectsOutputToContain('Extracted channel ID: UC_x5XG1OV2P6uZZ5FSM9Ttw')
        ->expectsOutputToContain("Channel 'Test Channel' added successfully!");

    $channel = Channel::where('channel_id', 'UC_x5XG1OV2P6uZZ5FSM9Ttw')->first();
    expect($channel)->not->toBeNull();
});

it('leaves channel URLs with @ unchanged', function (): void {
    Mail::fake();

    $this->mock(ExtractYouTubeChannelId::class, function (MockInterface $mock): void {
        $mock->shouldReceive('execute')
            ->once()
            ->with('https://www.youtube.com/@testchannel', false)
            ->andReturn('UC_x5XG1OV2P6uZZ5FSM9Ttw');
    });

    $this->artisan(AddChannelCommand::class)
        ->expectsQuestion('Channel label?', 'Test Channel')
        ->expectsQuestion('Channel URL/@handle?', 'https://www.youtube.com/@testchannel')
        ->expectsOutputToContain('Extracting channel ID from: https://www.youtube.com/@testchannel')
        ->expectsOutputToContain('Extracted channel ID: UC_x5XG1OV2P6uZZ5FSM9Ttw')
        ->expectsOutputToContain("Channel 'Test Channel' added successfully!");

    $channel = Channel::where('channel_id', 'UC_x5XG1OV2P6uZZ5FSM9Ttw')->first();
    expect($channel)->not->toBeNull();
});

it('leaves channel/custom/user URLs unchanged', function (): void {
    Mail::fake();

    $this->mock(ExtractYouTubeChannelId::class, function (MockInterface $mock): void {
        $mock->shouldReceive('execute')
            ->once()
            ->with('https://www.youtube.com/channel/UC_x5XG1OV2P6uZZ5FSM9Ttw', false)
            ->andReturn('UC_x5XG1OV2P6uZZ5FSM9Ttw');
    });

    $this->artisan(AddChannelCommand::class)
        ->expectsQuestion('Channel label?', 'Test Channel')
        ->expectsQuestion('Channel URL/@handle?', 'https://www.youtube.com/channel/UC_x5XG1OV2P6uZZ5FSM9Ttw')
        ->expectsOutputToContain('Extracting channel ID from: https://www.youtube.com/channel/UC_x5XG1OV2P6uZZ5FSM9Ttw')
        ->expectsOutputToContain('Extracted channel ID: UC_x5XG1OV2P6uZZ5FSM9Ttw')
        ->expectsOutputToContain("Channel 'Test Channel' added successfully!");

    $channel = Channel::where('channel_id', 'UC_x5XG1OV2P6uZZ5FSM9Ttw')->first();
    expect($channel)->not->toBeNull();
});

it('shows a message if the label name has already been taken', function (): void {
    $channel = Channel::factory()->create(['name' => 'Existing Channel']);

    $this->artisan(AddChannelCommand::class)
        ->expectsQuestion('Channel label?', 'Existing Channel')
        ->expectsOutputToContain('The answer has already been taken.');

    expect(Channel::count())->toBe(1)
        ->and(Channel::first()->id)->toBe($channel->id)
        ->and(Channel::first()->name)->toBe('Existing Channel');
});

it('does not allow spaces in channel url field', function (): void {
    $this->artisan(AddChannelCommand::class)
        ->expectsQuestion('Channel label?', 'Test Channel')
        ->expectsQuestion('Channel URL/@handle?', 'https://www.youtube.com/@test channel')
        ->expectsOutputToContain('The answer cannot contain spaces.');

    expect(Channel::count())->toBe(0);
});
