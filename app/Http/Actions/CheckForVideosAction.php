<?php

namespace App\Http\Actions;

use App\Mail\NewVideoMail;
use App\Models\Channel;
use App\Models\Video;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Class CheckForVideosAction
 *
 * This class is responsible for checking a YouTube channel's RSS feed for new videos,
 * extracting new videos that have not been previously recorded, and notifying via email
 * if new videos are found.
 */
class CheckForVideosAction
{
    /**
     * Executes the action to check for new videos for a given channel.
     *
     * @param  Channel  $channel  The channel to check for new videos.
     */
    public function execute(Channel $channel): void
    {
        $rssData = $this->fetchRssFeed($channel);
        if (is_null($rssData)) {
            return;
        }

        $newVideos = $this->extractNewVideos($rssData, $channel);
        if (empty($newVideos)) {
            return;
        }

        is_null($channel->last_checked_at)
            ? $this->firstTimeImport($newVideos, $channel)
            : $this->insertNewVideosAndNotify($newVideos, $channel);

        $this->updateChannelLastChecked($channel);
    }

    /**
     * Fetches the RSS feed for a given channel.
     *
     * @param  Channel  $channel  The channel for which to fetch the RSS feed.
     * @return object|null The RSS feed data or null if the fetch failed or no videos found.
     */
    private function fetchRssFeed(Channel $channel): ?object
    {
        $rssUrl = sprintf('https://www.youtube.com/feeds/videos.xml?channel_id=%s', $channel->channel_id);
        $response = Http::get($rssUrl);

        if ($response->failed()) {
            Log::error("Failed to fetch RSS feed for channel: {$channel->name}. Response: {$response->body()}");

            return null;
        }

        $rssData = simplexml_load_string($response->body());

        if (! $rssData || ! isset($rssData->entry)) {
            Log::info("No videos found in RSS feed for channel: {$channel->name}.");

            return null;
        }

        return $rssData;
    }

    /**
     * Extracts new videos from the RSS feed data and filters out videos that already exist or contain the word "LIVE".
     *
     * @param  object  $rssData  The RSS feed data.
     * @param  Channel  $channel  The channel to which the videos belong.
     * @return array An array of new videos.
     */
    private function extractNewVideos(object $rssData, Channel $channel): array
    {
        $existingVideoIds = Video::where('channel_id', $channel->id)->pluck('video_id')->toArray();
        $newVideos = [];

        foreach ($rssData->entry as $entry) {
            $videoId = str_replace('yt:video:', '', (string) $entry->id);
            $title = (string) $entry->title;

            // Check if the title contains the exact word "LIVE"
            if (stripos($title, 'LIVE') !== false && preg_match('/\bLIVE\b/', $title)) {
                Log::warning("Video with title containing 'LIVE' found and ignored: {$title}");

                continue;
            }

            if (in_array($videoId, $existingVideoIds, true)) {
                continue;
            }

            $newVideos[] = [
                'video_id' => $videoId,
                'title' => $title,
                'description' => (string) $entry->summary,
                'published_at' => Carbon::parse((string) $entry->published),
                'channel_id' => $channel->id,
            ];
        }

        return $newVideos;
    }

    /**
     * Performs the first-time import of new videos for a channel.
     *
     * @param  array  $newVideos  An array of new videos to insert.
     * @param  Channel  $channel  The channel to which the videos belong.
     */
    private function firstTimeImport(array $newVideos, Channel $channel): void
    {
        Video::insert($newVideos);
        Log::info("First-time import for channel: {$channel->name} completed with ".count($newVideos).' videos.');
    }

    /**
     * Inserts new videos into the database and sends notification emails.
     *
     * @param  array  $newVideos  An array of new videos to insert and notify.
     * @param  Channel  $channel  The channel to which the videos belong.
     */
    private function insertNewVideosAndNotify(array $newVideos, Channel $channel): void
    {
        foreach ($newVideos as $videoData) {
            $video = Video::create($videoData);
            Mail::to(config('app.alert_email'))->send(new NewVideoMail($video));
            Log::info("New video added: {$video->title} ({$video->video_id}) for channel: {$channel->name}.");
        }
    }

    /**
     * Updates the last checked timestamp for a channel.
     *
     * @param  Channel  $channel  The channel to update the last checked timestamp for.
     */
    private function updateChannelLastChecked(Channel $channel): void
    {
        $channel->update(['last_checked_at' => now()]);
        Log::info("Check for videos completed for channel: {$channel->name}.");
    }
}
