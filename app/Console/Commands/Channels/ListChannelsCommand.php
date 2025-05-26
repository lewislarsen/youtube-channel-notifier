<?php

declare(strict_types=1);

namespace App\Console\Commands\Channels;

use App\Models\Channel;
use Carbon\Carbon;
use Illuminate\Console\Command;

/**
 * Class ListChannelsCommand
 *
 * This command lists all the YouTube channels stored in the database, displaying
 * details such as the channel name, number of videos stored, last video grabbed time,
 * and the channel URL.
 */
class ListChannelsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'channels:list';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Lists all the channels that have been stored in the database';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $channels = Channel::orderBy('created_at', 'desc')->get();

        $this->table(
            ['Name', 'Videos Stored', 'Last Video Grabbed', 'Last Notification', 'Channel URL', 'Muted'],
            $channels->map(function (Channel $channel) {

                $latestNotifiedVideo = $channel->videos()
                    ->whereNotNull('notified_at')
                    ->orderBy('notified_at', 'desc')
                    ->first();

                return [
                    $channel->name,
                    $channel->videos()->count(),
                    Carbon::parse($channel->last_checked_at)->diffForHumans(),
                    $latestNotifiedVideo
                        ? Carbon::parse($latestNotifiedVideo->notified_at)->diffForHumans()
                        : '—',
                    $channel->getChannelUrl(),
                    $channel->isMuted() ? '✔' : '✘',
                ];
            })->toArray()
        );
    }
}
