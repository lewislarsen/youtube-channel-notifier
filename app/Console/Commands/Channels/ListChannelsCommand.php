<?php

declare(strict_types=1);

namespace App\Console\Commands\Channels;

use App\Models\Channel;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

/**
 * Class ListChannelsCommand
 *
 * This command lists all the YouTube channels stored in the database, displaying
 * details such as the channel name, number of videos stored, last video grabbed time,
 * and the channel URL.
 */
#[Description('Lists all the channels that have been stored in the database')]
#[Signature('channels:list')]
class ListChannelsCommand extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $channels = Channel::orderBy('created_at', 'desc')->get();

        $this->table(
            ['Name', 'Videos Stored', 'Last Video Grabbed', 'Last Notification', 'Channel URL', 'Muted', 'Note'],
            $channels->map(function (Channel $channel) {

                $latestNotifiedVideo = $channel->videos()
                    ->whereNotNull('notified_at')
                    ->latest('notified_at')
                    ->first();

                return [
                    $channel->name,
                    $channel->videos()->count(),
                    $channel->last_checked_at?->setTimezone(config('app.user_timezone'))->diffForHumans() ?? '—',
                    $latestNotifiedVideo?->notified_at?->setTimezone(config('app.user_timezone'))->diffForHumans() ?? '—',
                    $channel->getChannelUrl(),
                    $channel->isMuted() ? '✔' : '✘',
                    $channel->note ?: '—',
                ];
            })->toArray()
        );
    }
}
