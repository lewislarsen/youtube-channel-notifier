<?php

declare(strict_types=1);

namespace App\Actions\YouTube;

use App\Actions\Notifications\SendVideoNotifications;
use App\Models\Channel;
use App\Models\Video;
use Illuminate\Support\Facades\Log;

/**
 * Class ProcessVideos
 *
 * This action processes new videos based on channel settings.
 */
readonly class ProcessVideos
{
    public function __construct(private SendVideoNotifications $sendVideoNotifications) {}

    /**
     * Process new videos based on whether this is a first-time import or not.
     *
     * @param  array<int, array<string, mixed>>  $newVideos  An array of new videos.
     * @param  Channel  $channel  The channel to which the videos belong.
     */
    public function execute(array $newVideos, Channel $channel): void
    {
        is_null($channel->last_checked_at)
            ? $this->handleFirstTimeImport($newVideos, $channel)
            : $this->handleNewVideos($newVideos, $channel);
    }

    /**
     * Handle the first-time import of videos for a channel.
     *
     * @param  array<int, array<string, mixed>>  $newVideos  An array of new videos to insert.
     * @param  Channel  $channel  The channel to which the videos belong.
     */
    private function handleFirstTimeImport(array $newVideos, Channel $channel): void
    {
        Video::insert($newVideos);
        Log::info("First-time import for channel: {$channel->name} completed with ".count($newVideos).' videos.');
    }

    /**
     * Handle new videos based on the channel's notification settings.
     *
     * @param  array<int, array<string, mixed>>  $newVideos  An array of new videos to insert.
     * @param  Channel  $channel  The channel to which the videos belong.
     */
    private function handleNewVideos(array $newVideos, Channel $channel): void
    {
        foreach ($newVideos as $newVideo) {
            $video = Video::create($newVideo);

            if ($channel->isMuted()) {
                Log::info("New video added (notifications suppressed - channel muted): {$video->title} ({$video->video_id}) for channel: {$channel->name}.");

                continue;
            }

            $this->sendVideoNotifications->execute($video);
            Log::info("New video added and notifications sent: {$video->title} ({$video->video_id}) for channel: {$channel->name}.");
        }
    }
}
