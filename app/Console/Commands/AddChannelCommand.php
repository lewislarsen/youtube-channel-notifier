<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Actions\CheckForVideosAction;
use App\Models\Channel;
use Illuminate\Console\Command;

/**
 * Class AddChannelCommand
 *
 * This command is responsible for adding a new YouTube channel to the database
 * and performing an initial video import for the newly added channel.
 */
class AddChannelCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'channels:add';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add a new YouTube channel and perform an initial video import.';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $name = $this->ask('Enter the channel name');
        $channelId = $this->ask('Enter the channel ID');

        if (Channel::where('channel_id', $channelId)->exists()) {
            $this->components->error('A channel with this ID already exists in the database.');

            return;
        }

        $channel = Channel::create([
            'name' => $name,
            'channel_id' => $channelId,
            'last_checked_at' => null,
        ]);

        $this->components->success("Channel '{$channel->name}' added successfully!");

        $this->components->info("Running initial video import for '{$channel->name}'...");
        app(CheckForVideosAction::class)->execute($channel);

        $this->components->success('Initial import completed successfully.');
    }
}
