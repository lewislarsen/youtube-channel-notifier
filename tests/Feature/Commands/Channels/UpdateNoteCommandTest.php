<?php

declare(strict_types=1);

use App\Console\Commands\Channels\UpdateNoteCommand;
use App\Models\Channel;

it('can add a note to a channel that has no existing note', function (): void {
    $channel = Channel::factory()->create(['name' => 'Test Channel', 'note' => null]);

    $this->artisan(UpdateNoteCommand::class)
        ->expectsQuestion('Channel name?', 'Test Channel')
        ->expectsQuestion('Channel note', 'This is my new note')
        ->expectsOutputToContain("A note has been added to channel 'Test Channel'.");

    $channel = $channel->refresh();

    $this->assertEquals('This is my new note', $channel->note);
});

it('can update an existing note on a channel', function (): void {
    $channel = Channel::factory()->create([
        'name' => 'Test Channel',
        'note' => 'Original note content',
    ]);

    $this->artisan(UpdateNoteCommand::class)
        ->expectsQuestion('Channel name?', 'Test Channel')
        ->expectsQuestion('Channel note', 'Updated note content')
        ->expectsOutputToContain("The note for channel 'Test Channel' has been updated.");

    $channel = $channel->refresh();

    $this->assertEquals('Updated note content', $channel->note);
});

it('can remove a note from a channel by submitting empty text', function (): void {
    $channel = Channel::factory()->create([
        'name' => 'Test Channel',
        'note' => 'Existing note to be removed',
    ]);

    $this->artisan(UpdateNoteCommand::class)
        ->expectsQuestion('Channel name?', 'Test Channel')
        ->expectsQuestion('Channel note', '')
        ->expectsOutputToContain("The note for channel 'Test Channel' has been removed.");

    $channel = $channel->refresh();

    $this->assertNull($channel->note);
});

it('shows no changes message when submitting empty text for channel with no existing note', function (): void {
    $channel = Channel::factory()->create(['name' => 'Test Channel', 'note' => null]);

    $this->artisan(UpdateNoteCommand::class)
        ->expectsQuestion('Channel name?', 'Test Channel')
        ->expectsQuestion('Channel note', '')
        ->expectsOutputToContain("No changes made to channel 'Test Channel'.");

    $channel = $channel->refresh();

    $this->assertNull($channel->note);
});

it('displays current note when channel has an existing note', function (): void {
    $channel = Channel::factory()->create([
        'name' => 'Test Channel',
        'note' => 'My existing note',
    ]);

    $this->artisan(UpdateNoteCommand::class)
        ->expectsQuestion('Channel name?', 'Test Channel')
        ->expectsOutputToContain("Current note for 'Test Channel':")
        ->expectsOutputToContain('My existing note')
        ->expectsQuestion('Channel note', 'Updated note')
        ->expectsOutputToContain("The note for channel 'Test Channel' has been updated.");
});

it('displays message when channel has no existing note', function (): void {
    $channel = Channel::factory()->create(['name' => 'Test Channel', 'note' => null]);

    $this->artisan(UpdateNoteCommand::class)
        ->expectsQuestion('Channel name?', 'Test Channel')
        ->expectsOutputToContain("No note currently exists for 'Test Channel'.")
        ->expectsQuestion('Channel note', 'New note')
        ->expectsOutputToContain("A note has been added to channel 'Test Channel'.");
});

it('outputs a message if it cannot find a channel', function (): void {
    $this->artisan(UpdateNoteCommand::class)
        ->expectsQuestion('Channel name?', 'does-not-exist')
        ->expectsOutputToContain('A channel cannot be found with that name. Please run `php artisan channels:list`.');

    $this->assertDatabaseMissing('channels', [
        'name' => 'does-not-exist',
    ]);
});

it('handles multi-line notes correctly', function (): void {
    $channel = Channel::factory()->create(['name' => 'Test Channel', 'note' => null]);

    $multiLineNote = "This is line one\nThis is line two\nThis is line three";

    $this->artisan(UpdateNoteCommand::class)
        ->expectsQuestion('Channel name?', 'Test Channel')
        ->expectsQuestion('Channel note', $multiLineNote)
        ->expectsOutputToContain("A note has been added to channel 'Test Channel'.");

    $channel = $channel->refresh();

    $this->assertEquals($multiLineNote, $channel->note);
});

it('handles whitespace-only input as empty note', function (): void {
    $channel = Channel::factory()->create([
        'name' => 'Test Channel',
        'note' => 'Existing note',
    ]);

    $this->artisan(UpdateNoteCommand::class)
        ->expectsQuestion('Channel name?', 'Test Channel')
        ->expectsQuestion('Channel note', '   ')
        ->expectsOutputToContain("The note for channel 'Test Channel' has been removed.");

    $channel = $channel->refresh();

    $this->assertNull($channel->note);
});
