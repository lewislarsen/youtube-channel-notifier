<?php

declare(strict_types=1);

use App\Console\Commands\Channels\RenameChannelCommand;
use App\Models\Channel;

it('can rename a channel', function (): void {
    $channel = Channel::factory()->create(['name' => 'First Name']);

    $this->artisan(RenameChannelCommand::class)
        ->expectsQuestion('Current channel name?', 'First Name')
        ->expectsQuestion('New channel name?', 'Second Name')
        ->expectsOutputToContain("The channel '{$channel->name}' has been renamed to 'Second Name'.");

    $channel = $channel->refresh();

    $this->assertEquals($channel->name, 'Second Name');
});

it('outputs a message if it cannot find a channel', function (): void {
    $this->artisan(RenameChannelCommand::class)
        ->expectsQuestion('Current channel name?', 'does-not-exist')
        ->expectsOutputToContain('A channel cannot be found with that name. Please run `php artisan channels:list`.');

    $this->assertDatabaseMissing('channels', [
        'name' => 'does-not-exist',
    ]);
});
