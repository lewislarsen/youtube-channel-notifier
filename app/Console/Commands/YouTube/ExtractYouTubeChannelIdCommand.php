<?php

declare(strict_types=1);

namespace App\Console\Commands\YouTube;

use App\Actions\YouTube\DebuggableExtractYouTubeChannelId;
use Exception;
use Illuminate\Console\Command;

class ExtractYouTubeChannelIdCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'youtube:extract-channel-id
                            {channelUrl : The YouTube channel URL (@username or full URL)}
                            {--debug : Enable debug mode to see detailed information}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Extract the channel ID from a YouTube channel';

    /**
     * Execute the console command.
     */
    public function handle(DebuggableExtractYouTubeChannelId $debuggableExtractYouTubeChannelId): void
    {
        $channelUrl = $this->argument('channelUrl');
        $debugMode = $this->option('debug');

        $this->components->info("Searching for channel ID in: {$channelUrl}");

        try {
            if ($debugMode) {
                $debuggableExtractYouTubeChannelId->setDebugCallback(function ($message, $type = 'info'): void {
                    if ($type === 'warn') {
                        $this->components->warn($message);
                    } else {
                        $this->components->info($message);
                    }
                });
            }

            $channelId = $debuggableExtractYouTubeChannelId->execute($channelUrl, $debugMode);

            $channelLink = "https://www.youtube.com/channel/{$channelId}";
            $this->components->success("Found channel ID: {$channelId}");
            $this->components->info("Channel link: {$channelLink}");

            return;
        } catch (Exception $e) {
            $this->components->error("An error occurred: {$e->getMessage()}");

            if ($debugMode) {
                $this->components->error("Exception trace: {$e->getTraceAsString()}");
            }

            return;
        }
    }
}
