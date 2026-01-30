<?php

declare(strict_types=1);

namespace App\Console\Commands\Channels;

use App\Actions\CheckForVideos;
use App\Actions\YouTube\ExtractYouTubeChannelId;
use App\Models\Channel;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

use function Laravel\Prompts\text;

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
        $name = text(
            label: 'Channel label?',
            required: true,
            validate: [
                'required',
                'string',
                'max:255',
                Rule::unique('channels', 'name'),
            ],
            hint: 'Please enter a memorable name for the channel; usually the channels name on YouTube.'
        );

        $channelUrl = text(
            label: 'Channel URL/@handle?',
            required: true,
            validate: [
                'required',
                'string',
                function ($attribute, $value, $fail): void {
                    if (str_contains($value, ' ')) {
                        $fail('The :attribute cannot contain spaces.');
                    }
                },
            ],
            hint: 'Enter the channel URL or handle (e.g., https://www.youtube.com/@channelname or @channelname)',
        );

        $channelUrl = $this->formatChannelUrl($channelUrl);

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
            $channelId = text(
                label: 'Channel ID?',
                required: true,
                hint: 'Please consult the README on GitHub for more information.',
            );
        }

        $this->createChannel($name, $channelId);
    }

    /**
     * Format channel URL to ensure it has proper format with @ for handles.
     */
    private function formatChannelUrl(string $channelUrl): string
    {
        $channelUrl = trim($channelUrl);

        if (empty($channelUrl) || Str::startsWith($channelUrl, '@')) {
            return $channelUrl;
        }

        if (Str::startsWith($channelUrl, ['http://', 'https://', 'www.'])) {
            return $this->formatYouTubeUrl($channelUrl);
        }

        return '@'.$channelUrl;
    }

    /**
     * Format a full YouTube URL to ensure proper @ format if needed.
     */
    private function formatYouTubeUrl(string $url): string
    {
        if (! Str::contains($url, 'youtube.com/')) {
            return $url;
        }

        if (Str::contains($url, '@') ||
            Str::contains($url, ['channel/', 'c/', 'user/'])) {
            return $url;
        }

        $parts = explode('/', rtrim($url, '/'));
        $lastPart = end($parts);

        if (empty($lastPart)) {
            return $url;
        }

        return Str::replaceLast($lastPart, '@'.$lastPart, $url);
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
        resolve(CheckForVideos::class)->execute($channel);
        $this->components->success('Initial import completed successfully.');
    }
}
