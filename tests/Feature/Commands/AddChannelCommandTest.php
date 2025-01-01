<?php

use App\Console\Commands\AddChannelCommand;
use App\Models\Channel;
use App\Models\Video;
use Illuminate\Support\Facades\Mail;

beforeEach(function () {
    Channel::truncate();
    Video::truncate();
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

    // Assert that the channel was added to the database
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
