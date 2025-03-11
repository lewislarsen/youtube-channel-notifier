<?php

declare(strict_types=1);

namespace App\Console\Commands\Channels;

use App\Actions\CheckForVideosAction;
use App\Actions\YouTube\ExtractYouTubeChannelId;
use App\Models\Channel;
use Exception;
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
    protected $signature = 'channels:add {--debug : Enable debug mode to see detailed information}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add a new YouTube channel and perform an initial video import.';

    /**
     * Execute the console command.
     */
    public function handle(ExtractYouTubeChannelId $extractYouTubeChannelId): void
    {
        $name = $this->ask('Enter the channel name');
        $channelUrl = $this->ask('Enter the channel URL or handle (e.g., https://www.youtube.com/@channelname or @channelname)');

        $this->components->info("Extracting channel ID from: {$channelUrl}");

        try {
            $debugMode = $this->option('debug');
            $channelId = $extractYouTubeChannelId->execute($channelUrl, $debugMode);

            $this->components->info("Extracted channel ID: {$channelId}");
        } catch (Exception $e) {
            $this->components->error("Failed to automatically extract channel ID: {$e->getMessage()}");

            if ($debugMode) {
                $this->components->error("Exception trace: {$e->getTraceAsString()}");
            }

            $this->components->info('Falling back to manual channel ID entry.');
            $channelId = $this->ask('Please enter the channel ID manually');

            if (empty($channelId)) {
                $this->components->error('Channel ID is required.');

                return;
            }
        }

        $this->createChannel($name, $channelId);
    }

    /**
     * Check if channel exists and create if not.
     */
    private function createChannel(string $name, string $channelId): void
    {
        if ($this->channelExists($channelId)) {
            return;
        }

        $channel = Channel::create([
            'name' => $name,
            'channel_id' => $channelId,
            'last_checked_at' => null,
        ]);

        $this->components->success("Channel '{$channel->name}' added successfully!");

        $this->importVideos($channel);
    }

    /**
     * Check if a channel with the given ID already exists.
     */
    private function channelExists(string $channelId): bool
    {
        if (Channel::where('channel_id', $channelId)->exists()) {
            $this->components->error('A channel with this ID already exists in the database.');

            return true;
        }

        return false;
    }

    /**
     * Import videos for the given channel.
     */
    private function importVideos(Channel $channel): void
    {
        $this->components->info("Running initial video import for '{$channel->name}'...");
        app(CheckForVideosAction::class)->execute($channel);
        $this->components->success('Initial import completed successfully.');
    }
}
