<?php

namespace App\Console\Commands;

use App\Http\Actions\CheckForVideosAction;
use App\Models\Channel;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CheckChannelsCommand extends Command
{
    protected $signature = 'channel:check';

    protected $description = 'Check all channels for new videos and send notifications if necessary';

    public function handle(): int
    {
        Log::info('[CHECKING] Performing a check to see if channels have any new content.');

        $channels = Channel::all();

        if ($channels->isEmpty()) {
            $this->info('No channels found in the database.');

            return 0;
        }

        $this->info('Checking channels for new videos...');

        $action = app(CheckForVideosAction::class);

        foreach ($channels as $channel) {
            $this->info("Checking channel: {$channel->name} ({$channel->channel_id})");
            $action->execute($channel);
        }

        $this->info('Channel check completed.');

        return 0;
    }
}
