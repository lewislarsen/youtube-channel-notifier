<?php

namespace App\Console\Commands;

use App\Http\Actions\CheckForVideosAction;
use App\Models\Channel;
use Illuminate\Console\Command;

class AddChannelCommand extends Command
{
    protected $signature = 'channel:add';

    protected $description = 'Add a new YouTube channel and perform an initial video import.';

    public function handle(): int
    {
        $name = $this->ask('Enter the channel name');
        $channelId = $this->ask('Enter the channel ID');

        if (Channel::where('channel_id', $channelId)->exists()) {
            $this->error('A channel with this ID already exists in the database.');

            return 1;
        }

        $channel = Channel::create([
            'name' => $name,
            'channel_id' => $channelId,
            'last_checked_at' => null, // Ensure it's treated as a first-time import
        ]);

        $this->info("Channel '{$channel->name}' added successfully!");

        $this->info("Running initial video import for '{$channel->name}'...");
        app(CheckForVideosAction::class)->execute($channel);

        $this->info('Initial import completed successfully.');

        return 0;
    }
}
