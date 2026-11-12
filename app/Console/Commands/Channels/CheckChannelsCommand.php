<?php

declare(strict_types=1);

namespace App\Console\Commands\Channels;

use App\Actions\CheckForVideos;
use App\Models\Channel;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * Class CheckChannelsCommand
 *
 * This command is responsible for checking all YouTube channels in the database
 * for new videos and sending notifications if new videos are found.
 */
#[Description('Check all channels for new videos and send notifications if necessary')]
#[Signature('channels:check')]
class CheckChannelsCommand extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $channels = Channel::all();

        if ($channels->isEmpty()) {
            $this->components->error('No channels found in the database.');

            return;
        }

        $this->components->info('Checking channels for new videos...');

        $checkForVideosAction = resolve(CheckForVideos::class);

        foreach ($channels as $channel) {
            Log::debug('Checking channel "' . $channel->name . '" for new videos...');
            $this->components->info("Checking channel: {$channel->name} ({$channel->channel_id})");
            $checkForVideosAction->execute($channel);
        }

        $this->components->success('Channel check completed.');

    }
}
