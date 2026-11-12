<?php

declare(strict_types=1);

namespace App\Console\Commands\Channels;

use App\Models\Channel;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Database\Console\Migrations\BaseCommand;

use function Laravel\Prompts\suggest;

#[Description('Toggle the mute status of a channel; this prevents notifications being sent.')]
#[Signature('channels:mute {name?}')]
class MuteChannelCommand extends BaseCommand
{
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

        if (! $channel instanceof Channel) {
            $this->components->error('A channel cannot be found with that name. Please run `php artisan channels:list`.');

            return;
        }

        $channel->toggleMute();

        /** @var Channel $freshChannel */
        $freshChannel = $channel->fresh();

        if ($freshChannel->isMuted()) {
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
