<?php

declare(strict_types=1);

namespace App\Console\Commands\Channels;

use App\Models\Channel;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;

use function Laravel\Prompts\suggest;
use function Laravel\Prompts\textarea;

/**
 * Class UpdateNoteCommand
 *
 * This command is responsible for updating the note of a channel you've already added.
 */
class UpdateNoteCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'channels:note';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update the note for a channel you have already added.';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $channelName = suggest(
            label: 'Channel name?',
            options: fn (string $value) => $value !== ''
                ? Channel::query()->where(function (Builder $builder) use ($value): void {
                    $builder->where('name', 'like', "%{$value}%");
                })->pluck('name', 'id')->all()
                : [],
            required: true,
            hint: 'Select the channel you want to update the note for.',
        );

        $channel = $this->findChannel($channelName);

        if (! $channel) {
            $this->components->error('A channel cannot be found with that name. Please run `php artisan channels:list`.');

            return;
        }

        $currentNote = $channel->getAttribute('note');

        // Display current note if it exists
        if ($currentNote) {
            $this->components->info("Current note for '{$channelName}':");
            $this->line($currentNote);
        } else {
            $this->components->info("No note currently exists for '{$channelName}'.");
        }
        $this->newLine();

        $newNote = textarea(
            label: 'Channel note',
            placeholder: $currentNote ?: 'Enter your note here...',
            default: $currentNote ?: '',
            required: false,
            hint: 'Enter the note for this channel. Leave empty to remove the note.',
        );

        // Handle empty note (remove note)
        if (empty(trim($newNote))) {
            if ($currentNote) {
                if (! $this->updateChannelNote($channel, null)) {
                    $this->components->error('Failed to remove the note.');

                    return;
                }
                $this->components->success("The note for channel '{$channelName}' has been removed.");
            } else {
                $this->components->info("No changes made to channel '{$channelName}'.");
            }

            return;
        }

        // Update the note
        if (! $this->updateChannelNote($channel, $newNote)) {
            $this->components->error('Failed to update the note.');

            return;
        }

        if ($currentNote) {
            $this->components->success("The note for channel '{$channelName}' has been updated.");
        } else {
            $this->components->success("A note has been added to channel '{$channelName}'.");
        }
    }

    /**
     * Find a channel by name.
     */
    protected function findChannel(string $channelName): ?Channel
    {
        return Channel::query()->where('name', $channelName)->first();
    }

    /**
     * Update a channel's note.
     */
    protected function updateChannelNote(Channel $channel, ?string $note): bool
    {
        $channel->forceFill([
            'note' => $note,
        ]);

        return $channel->save();
    }
}
