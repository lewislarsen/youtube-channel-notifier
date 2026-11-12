<?php

declare(strict_types=1);

namespace App\Console\Commands\Channels;

use App\Models\Channel;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;

use function Laravel\Prompts\suggest;
use function Laravel\Prompts\text;

/**
 * Class RenameChannelCommand
 *
 * This command is responsible for renaming a channel you've already added.
 */
#[Description('Rename a channel you have already added.')]
#[Signature('channels:rename')]
class RenameChannelCommand extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $channelName = suggest(
            label: 'Current channel name?',
            options: fn (string $value) => $value !== ''
                ? Channel::query()->where(function (Builder $builder) use ($value): void {
                    $builder->where('name', 'like', "%{$value}%");
                })->pluck('name', 'id')->all()
                : [],
            required: true,
            hint: 'This is the current of the channel you want to rename.',
        );

        $channel = $this->findChannel($channelName);

        if (! $channel instanceof Channel) {
            $this->components->error('A channel cannot be found with that name. Please run `php artisan channels:list`.');

            return;
        }

        $newName = text(
            label: 'New channel name?',
            required: true,
            hint: 'Please enter the new channel name.'
        );

        if ($this->channelNameExists($newName, $channel->getAttribute('id'))) {
            $this->components->error("A channel with the name '{$newName}' already exists. Channel names must be unique.");

            return;
        }

        if (! $this->renameChannel($channel, $newName)) {
            $this->components->error('Failed to rename the channel.');

            return;
        }

        $this->components->success("The channel '{$channelName}' has been renamed to '{$newName}'.");
    }

    /**
     * Find a channel by name.
     */
    protected function findChannel(string $channelName): ?Channel
    {
        return Channel::query()->where('name', $channelName)->first();
    }

    /**
     * Check if a channel name already exists for another channel.
     */
    protected function channelNameExists(string $name, string|int $excludeId): bool
    {
        return Channel::query()
            ->where('name', $name)
            ->where('id', '!=', $excludeId)
            ->exists();
    }

    /**
     * Rename a channel's name to something different.
     */
    protected function renameChannel(Channel $channel, string $newLabel): bool
    {
        $channel->forceFill([
            'name' => $newLabel,
        ]);

        return $channel->save();
    }
}
