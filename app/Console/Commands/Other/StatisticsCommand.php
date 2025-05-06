<?php

declare(strict_types=1);

namespace App\Console\Commands\Other;

use App\Models\Channel;
use App\Models\Video;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;

class StatisticsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'other:stats';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'View compiled statistics for the YouTube Channel Notifier instance.';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $channelCount = Channel::count();
        $videoCount = Video::count();

        if ($channelCount === 0) {
            $this->components->error('No channels found. Please add channels using `php artisan channels:add` to get statistics.');

            return;
        }

        $this->components->info('Fetching real-time statistics - no caching is active');
        $this->displayConsolidatedTable($channelCount, $videoCount);
    }

    /**
     * Display the consolidated statistics table.
     */
    private function displayConsolidatedTable(int $channelCount, int $videoCount): void
    {
        /** @var array<int, array<string, string>> $tableData */
        $tableData = [];

        $this->addTableSection($tableData, '📊 SYSTEM OVERVIEW', [
            'Monitored Channels' => $channelCount,
            'Total Videos Tracked' => $videoCount,
        ]);

        $mutedChannels = Channel::whereNotNull('muted_at')->count();
        $unmutedChannels = $channelCount - $mutedChannels;
        $this->addTableSection($tableData, '📺 CHANNEL STATISTICS', [
            'Active Channels' => $unmutedChannels,
            'Muted Channels' => $mutedChannels,
        ]);

        $latestChannel = Channel::latest('created_at')->first();
        if ($latestChannel && $latestChannel->created_at instanceof Carbon) {
            $this->addTableSection($tableData, '🆕 LATEST CHANNEL', [
                'Channel Name' => $latestChannel->name,
                'Channel URL' => $latestChannel->getChannelUrl(),
                'Added on' => $latestChannel->created_at->format('Y-m-d H:i:s').' ('.$this->humanReadableTime($latestChannel->created_at).')',
            ]);
        }

        $latestVideo = Video::latest('created_at')->first();
        if ($latestVideo && $latestVideo->created_at instanceof Carbon) {
            $this->addTableSection($tableData, '🎬 LATEST VIDEO', [
                'Video Title' => $latestVideo->title,
                'Video URL' => $latestVideo->getYoutubeUrl(),
                'Channel' => $latestVideo->channel->name ?? 'Unknown',
                'Added on' => $latestVideo->created_at->format('Y-m-d H:i:s').' ('.$this->humanReadableTime($latestVideo->created_at).')',
            ]);
        }

        $alertEmails = Config::get('app.alert_emails', []);
        $discordWebhook = Config::get('app.discord_webhook_url');
        $postWebhook = Config::get('app.webhook_post_url');
        $emailStatus = ! empty($alertEmails) ? 'Enabled' : 'Disabled';
        $discordStatus = ! empty($discordWebhook) ? 'Enabled' : 'Disabled';
        $postWebhookStatus = ! empty($postWebhook) ? 'Enabled' : 'Disabled';

        $notifications = [
            'Email Notifications' => $emailStatus,
            'Discord Notifications' => $discordStatus,
            'POST Webhook Notification' => $postWebhookStatus,
            'Total Active Methods' => (! empty($alertEmails) ? 1 : 0) + (! empty($discordWebhook) ? 1 : 0) + (! empty($postWebhook) ? 1 : 0),
        ];

        if (! empty($alertEmails)) {
            $notifications['Email Recipients'] = implode(', ', $alertEmails);
        }

        $this->addTableSection($tableData, '📧 NOTIFICATION SETTINGS', $notifications);

        if ($mutedChannels > 0) {
            $mutedChannelsInfo = [];
            Channel::whereNotNull('muted_at')
                ->get()
                ->each(function ($channel) use (&$mutedChannelsInfo): void {
                    if ($channel->muted_at instanceof Carbon) {
                        $channelInfo = $channel->name.' ('.$channel->getChannelUrl().')';
                        $mutedChannelsInfo['Since '.$channel->muted_at->format('Y-m-d H:i:s').
                        ' ('.$this->humanReadableTime($channel->muted_at).')'] = $channelInfo;
                    }
                });

            $this->addTableSection($tableData, '🔕 MUTED CHANNELS', $mutedChannelsInfo);
        }

        $this->table(['Section', 'Information', 'Details'], $tableData);
    }

    /**
     * Add a section to the table data
     *
     * @param  array<int, array<string, string>>  $tableData
     * @param  array<string, string|int>  $items
     *
     * @param-out array<int, array<string, string>> $tableData
     */
    private function addTableSection(array &$tableData, string $sectionTitle, array $items): void
    {
        $firstItem = true;

        foreach ($items as $key => $value) {
            $key = (string) $key;
            $value = (string) $value;

            $tableData[] = [
                'Section' => $firstItem ? $sectionTitle : '',
                'Information' => $key,
                'Details' => $value,
            ];

            $firstItem = false;
        }

        // empty row for spacing
        $tableData[] = [
            'Section' => '',
            'Information' => '',
            'Details' => '',
        ];
    }

    /**
     * Convert a datetime to a human-readable relative time string
     */
    private function humanReadableTime(Carbon $dateTime): string
    {
        return $dateTime->diffForHumans();
    }
}
