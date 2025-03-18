<?php

declare(strict_types=1);

namespace App\Console\Commands\Channels;

use App\Models\Channel;
use Illuminate\Console\Command;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\suggest;

/**
 * Class RemoveChannelCommand
 *
 * This command is responsible for removing an existing YouTube channel from the database
 * and deleting all stored data related to the channel.
 */
class RemoveChannelCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'channels:remove';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove a YouTube channel and all stored data.';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $channelName = suggest(
            label: 'Enter the channel name',
            options: fn (string $value) => $value !== ''
                ? Channel::whereLike('name', "%{$value}%")->pluck('name', 'id')->all()
                : [],
            placeholder: 'E.g. Settled',
            required: true,
            hint: 'This is the label of the channel you have set.'
        );

        $channel = $this->findChannel($channelName);

        if (! $channel) {
            $this->components->error('A channel cannot be found with that name. Please run `php artisan channels:list`.');

            return;
        }

        if (! $this->confirmRemoval($channelName)) {
            $this->components->info('Channel removal has been cancelled.');

            return;
        }

        $this->removeChannel($channel);

        $this->components->success("Channel '{$channelName}' has been removed.");
    }

    /**
     * Find a channel by name.
     */
    protected function findChannel(string $channelName): ?Channel
    {
        return Channel::where('name', $channelName)->first();
    }

    /**
     * Confirm the removal of a channel.
     */
    protected function confirmRemoval(string $channelName): bool
    {
        return confirm(
            label: "Are you sure you want to remove the channel '{$channelName}' and all related data?",
            default: false,
            yes: 'Remove channel',
            no: 'Do not remove channel'
        );
    }

    /**
     * Remove the given channel and its related data.
     */
    protected function removeChannel(Channel $channel): void
    {
        $channel->videos()->delete();
        $channel->delete();
    }
}
