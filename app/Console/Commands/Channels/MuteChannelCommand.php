<?php

declare(strict_types=1);

namespace App\Console\Commands\Channels;

use App\Models\Channel;
use Illuminate\Database\Console\Migrations\BaseCommand;

use function Laravel\Prompts\suggest;

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
        $channelName = $this->argument('name') ?? suggest(
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

        $channel->toggleMute();

        $channel = $channel->fresh();

        if ($channel->isMuted()) {
            $this->components->success("The channel '{$channelName}' has been muted. You will no longer receive notifications about their uploads.");

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
