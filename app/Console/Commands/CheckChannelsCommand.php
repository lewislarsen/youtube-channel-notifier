<?php

namespace App\Console\Commands;

use App\Actions\CheckForVideosAction;
use App\Models\Channel;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * Class CheckChannelsCommand
 *
 * This command is responsible for checking all YouTube channels in the database
 * for new videos and sending notifications if new videos are found.
 */
class CheckChannelsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'channels:check';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check all channels for new videos and send notifications if necessary';

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

        $action = app(CheckForVideosAction::class);

        foreach ($channels as $channel) {
            Log::debug('Checking channel "'.$channel->name.'" for new videos...');
            $this->components->info("Checking channel: {$channel->name} ({$channel->channel_id})");
            $action->execute($channel);
        }

        $this->components->success('Channel check completed.');

    }
}
