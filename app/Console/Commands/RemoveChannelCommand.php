<?php

namespace App\Console\Commands;

use App\Models\Channel;
use Illuminate\Console\Command;

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
    public function handle(): int
    {
        $channelName = $this->ask('Enter the channel name');

        $channel = $this->findChannel($channelName);
        if (! $channel) {
            $this->error('A channel cannot be found with that name. Please run `php artisan channels:list`.');

            return 1;
        }

        if (! $this->confirmRemoval($channelName)) {
            $this->info('Channel removal has been cancelled.');

            return 1;
        }

        $this->removeChannel($channel);

        $this->info("Channel '{$channelName}' has been removed.");

        return 0;
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
        return $this->confirm("Are you sure you want to remove the channel '{$channelName}' and all related data?");
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
