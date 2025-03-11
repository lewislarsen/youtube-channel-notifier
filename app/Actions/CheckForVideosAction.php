<?php

declare(strict_types=1);

namespace App\Actions;

use App\Mail\NewVideoMail;
use App\Models\Channel;
use App\Models\Video;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
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
            Log::info("No new valid videos found after filtering for channel: {$channel->name}.");

            return;
        }

        is_null($channel->last_checked_at)
            ? $this->firstTimeImport($newVideos, $channel)
            : $this->insertNewVideosAndNotify($newVideos, $channel);

        $channel->updateLastChecked();

        Log::debug("Check for videos completed for channel: {$channel->name}.");
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
     * Extracts new videos from the RSS feed data and filters out videos that already exist or contain any blocked words matching our filter.
     *
     * @param  object  $rssData  The RSS feed data.
     * @param  Channel  $channel  The channel to which the videos belong.
     * @return array An array of new videos.
     */
    private function extractNewVideos(object $rssData, Channel $channel): array
    {
        $existingVideoIds = Video::where('channel_id', $channel->id)->pluck('video_id')->toArray();
        $newVideos = [];
        $excludedWords = Config::get('excluded-video-words.skip_terms', []);

        // Return empty array early if no entries in feed
        if (! isset($rssData->entry) || count($rssData->entry) === 0) {
            return [];
        }

        foreach ($rssData->entry as $entry) {
            $videoId = str_replace('yt:video:', '', (string) $entry->id);
            $title = (string) $entry->title;

            // Skip videos that already exist in the database
            if (in_array($videoId, $existingVideoIds, true)) {
                Log::debug("Skipping existing video: {$title} ({$videoId})");

                continue;
            }

            // Check if title contains any excluded words
            $shouldSkip = false;
            foreach ($excludedWords as $excludedWord) {
                // Case-insensitive match
                if (stripos($title, (string) $excludedWord) !== false) {
                    Log::debug("Skipping video with excluded term '{$excludedWord}': {$title} ({$videoId})");
                    $shouldSkip = true;
                    break;
                }
            }

            if ($shouldSkip) {
                continue;
            }

            // Ensure we have all required fields before adding to newVideos
            if (! isset($entry->summary) || ! isset($entry->published)) {
                Log::warning("Skipping video with missing data: {$title} ({$videoId})");

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
        // Check if channel is muted before processing
        if ($channel->isMuted()) {
            // If channel is muted, just insert videos without sending notifications
            foreach ($newVideos as $newVideo) {
                $video = Video::create($newVideo);
                Log::info("New video added (notifications suppressed - channel muted): {$video->title} ({$video->video_id}) for channel: {$channel->name}.");
            }

            return;
        }

        $sendDiscordNotificationAction = app(SendDiscordNotificationAction::class);

        foreach ($newVideos as $newVideo) {
            $video = Video::create($newVideo);

            Mail::to(Config::get('app.alert_emails'))->send(new NewVideoMail($video, $channel));

            if (Config::get('app.discord_webhook_url')) {
                $sendDiscordNotificationAction->execute($video);
            }

            Log::info("New video added and notifications sent: {$video->title} ({$video->video_id}) for channel: {$channel->name}.");
        }
    }
}
