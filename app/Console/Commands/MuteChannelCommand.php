<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Channel;
use Illuminate\Database\Console\Migrations\BaseCommand;

class MuteChannelCommand extends BaseCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'channels:mute {name?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Toggle the mute status of a channel; this prevents notifications being sent.';

    public function handle(): void
    {
        $channelName = $this->argument('name') ?? $this->ask('Enter the channel name');

        $channel = $this->findChannel($channelName);

        if (! $channel) {
            $this->components->error('A channel cannot be found with that name. Please run `php artisan channels:list`.');

            return;
        }

        $channel->toggleMute();

        $channel = $channel->fresh();

        if ($channel->isMuted()) {
            $this->components->success("The channel '{$channelName}' has been muted. You will no longer receive video notifications.");

            return;
        }

        $this->components->success("The channel '{$channelName}' has been un-muted. You will start receiving notifications again.");
    }

    /**
     * Find a channel by name.
     */
    protected function findChannel(string $channelName): ?Channel
    {
        return Channel::where('name', $channelName)->first();
    }
}
