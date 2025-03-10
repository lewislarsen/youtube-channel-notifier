<?php

declare(strict_types=1);

use App\Console\Commands\AddChannelCommand;
use App\Models\Channel;
use App\Models\Video;
use Illuminate\Support\Facades\Mail;

beforeEach(function (): void {
    Channel::truncate();
    Video::truncate();
});

it('adds a new channel and performs an initial video import', function (): void {
    Mail::fake();

    $this->artisan(AddChannelCommand::class)
        ->expectsQuestion('Enter the channel name', 'Test Channel')
        ->expectsQuestion('Enter the channel ID', 'UC_x5XG1OV2P6uZZ5FSM9Ttw')
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

    $this->artisan(AddChannelCommand::class)
        ->expectsQuestion('Enter the channel name', 'Test Channel')
        ->expectsQuestion('Enter the channel ID', 'UC_x5XG1OV2P6uZZ5FSM9Ttw')
        ->expectsOutputToContain('A channel with this ID already exists in the database.');

    expect(Channel::count())->toBe(1);
});
