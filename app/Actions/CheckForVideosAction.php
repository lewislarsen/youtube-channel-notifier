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

        if ($this->hasNoNewVideos($newVideos, $channel)) {
            return;
        }

        $this->processNewVideos($newVideos, $channel);
        $this->updateChannelTimestamp($channel);
    }

    /**
     * Fetches the RSS feed for a given channel.
     *
     * @param  Channel  $channel  The channel for which to fetch the RSS feed.
     * @return object|null The RSS feed data or null if the fetch failed or no videos found.
     */
    private function fetchRssFeed(Channel $channel): ?object
    {
        $rssUrl = $this->buildRssUrl($channel);
        $response = Http::get($rssUrl);

        if ($response->failed()) {
            $this->logFailedFetch($channel, $response);

            return null;
        }

        $rssData = simplexml_load_string($response->body());

        if ($this->isInvalidRssData($rssData)) {
            $this->logNoVideosFound($channel);

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
        $existingVideoIds = $this->getExistingVideoIds($channel);
        $excludedWords = Config::get('excluded-video-words.skip_terms', []);
        $newVideos = [];

        if ($this->hasNoEntries($rssData)) {
            return [];
        }

        foreach ($rssData->entry as $entry) {
            $videoId = $this->extractVideoId($entry);
            $title = (string) $entry->title;

            if ($this->isExistingVideo($videoId, $existingVideoIds)) {
                $this->logSkippingExistingVideo($title, $videoId);

                continue;
            }

            if ($this->shouldSkipByTitle($title, $excludedWords)) {
                continue;
            }

            if ($this->hasMissingRequiredFields($entry)) {
                $this->logMissingDataWarning($title, $videoId);

                continue;
            }

            $newVideos[] = $this->createVideoData($entry, $channel, $videoId, $title);
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
        $this->logFirstTimeImport($channel, $newVideos);
    }

    /**
     * Inserts new videos into the database and sends notification emails.
     *
     * @param  array  $newVideos  An array of new videos to insert and notify.
     * @param  Channel  $channel  The channel to which the videos belong.
     */
    private function insertNewVideosAndNotify(array $newVideos, Channel $channel): void
    {
        if ($channel->isMuted()) {
            $this->insertVideosWithoutNotification($newVideos, $channel);

            return;
        }

        $this->insertVideosWithNotification($newVideos, $channel);
    }

    /**
     * Build the RSS feed URL for a channel.
     *
     * @param  Channel  $channel  The channel for which to build the URL.
     * @return string The RSS feed URL.
     */
    private function buildRssUrl(Channel $channel): string
    {
        return sprintf('https://www.youtube.com/feeds/videos.xml?channel_id=%s', $channel->channel_id);
    }

    /**
     * Log a failed fetch attempt.
     *
     * @param  Channel  $channel  The channel for which the fetch failed.
     * @param  mixed  $response  The HTTP response.
     */
    private function logFailedFetch(Channel $channel, $response): void
    {
        Log::error("Failed to fetch RSS feed for channel: {$channel->name}. Response: {$response->body()}");
    }

    /**
     * Check if the RSS data is invalid.
     *
     * @param  mixed  $rssData  The RSS data to check.
     * @return bool True if the data is invalid, false otherwise.
     */
    private function isInvalidRssData($rssData): bool
    {
        return ! $rssData || ! isset($rssData->entry);
    }

    /**
     * Log that no videos were found in the RSS feed.
     *
     * @param  Channel  $channel  The channel for which no videos were found.
     */
    private function logNoVideosFound(Channel $channel): void
    {
        Log::info("No videos found in RSS feed for channel: {$channel->name}.");
    }

    /**
     * Get existing video IDs for a channel.
     *
     * @param  Channel  $channel  The channel for which to get existing video IDs.
     * @return array An array of existing video IDs.
     */
    private function getExistingVideoIds(Channel $channel): array
    {
        return Video::where('channel_id', $channel->id)->pluck('video_id')->toArray();
    }

    /**
     * Check if the RSS data has no entries.
     *
     * @param  object  $rssData  The RSS data to check.
     * @return bool True if the data has no entries, false otherwise.
     */
    private function hasNoEntries(object $rssData): bool
    {
        return ! isset($rssData->entry) || count($rssData->entry) === 0;
    }

    /**
     * Extract a video ID from an RSS entry.
     *
     * @param  object  $entry  The RSS entry.
     * @return string The extracted video ID.
     */
    private function extractVideoId(object $entry): string
    {
        return str_replace('yt:video:', '', (string) $entry->id);
    }

    /**
     * Check if a video already exists.
     *
     * @param  string  $videoId  The video ID to check.
     * @param  array  $existingVideoIds  An array of existing video IDs.
     * @return bool True if the video exists, false otherwise.
     */
    private function isExistingVideo(string $videoId, array $existingVideoIds): bool
    {
        return in_array($videoId, $existingVideoIds, true);
    }

    /**
     * Log that an existing video is being skipped.
     *
     * @param  string  $title  The title of the video.
     * @param  string  $videoId  The ID of the video.
     */
    private function logSkippingExistingVideo(string $title, string $videoId): void
    {
        Log::debug("Skipping existing video: {$title} ({$videoId})");
    }

    /**
     * Check if a video title contains any excluded words.
     *
     * @param  string  $title  The title to check.
     * @param  array  $excludedWords  An array of excluded words.
     * @return bool True if the title should be skipped, false otherwise.
     */
    private function shouldSkipByTitle(string $title, array $excludedWords): bool
    {
        foreach ($excludedWords as $excludedWord) {
            if (stripos($title, (string) $excludedWord) !== false) {
                $this->logSkippingByExcludedWord($excludedWord, $title);

                return true;
            }
        }

        return false;
    }

    /**
     * Log that a video is being skipped because its title contains an excluded word.
     *
     * @param  string  $excludedWord  The excluded word.
     * @param  string  $title  The title of the video.
     */
    private function logSkippingByExcludedWord(string $excludedWord, string $title): void
    {
        Log::debug("Skipping video with excluded term '{$excludedWord}': {$title}");
    }

    /**
     * Check if an RSS entry is missing required fields.
     *
     * @param  object  $entry  The RSS entry to check.
     * @return bool True if the entry is missing required fields, false otherwise.
     */
    private function hasMissingRequiredFields(object $entry): bool
    {
        return ! isset($entry->summary, $entry->published);
    }

    /**
     * Log a warning that a video is being skipped because it has missing data.
     *
     * @param  string  $title  The title of the video.
     * @param  string  $videoId  The ID of the video.
     */
    private function logMissingDataWarning(string $title, string $videoId): void
    {
        Log::warning("Skipping video with missing data: {$title} ({$videoId})");
    }

    /**
     * Create video data from an RSS entry.
     *
     * @param  object  $entry  The RSS entry.
     * @param  Channel  $channel  The channel to which the video belongs.
     * @param  string  $videoId  The ID of the video.
     * @param  string  $title  The title of the video.
     * @return array The created video data.
     */
    private function createVideoData(object $entry, Channel $channel, string $videoId, string $title): array
    {
        return [
            'video_id' => $videoId,
            'title' => $title,
            'description' => (string) $entry->summary,
            'published_at' => Carbon::parse((string) $entry->published),
            'channel_id' => $channel->id,
        ];
    }

    /**
     * Check if there are no new videos.
     *
     * @param  array  $newVideos  An array of new videos.
     * @param  Channel  $channel  The channel to which the videos belong.
     * @return bool True if there are no new videos, false otherwise.
     */
    private function hasNoNewVideos(array $newVideos, Channel $channel): bool
    {
        if (empty($newVideos)) {
            Log::info("No new valid videos found after filtering for channel: {$channel->name}.");

            return true;
        }

        return false;
    }

    /**
     * Process new videos based on whether this is a first-time import or not.
     *
     * @param  array  $newVideos  An array of new videos.
     * @param  Channel  $channel  The channel to which the videos belong.
     */
    private function processNewVideos(array $newVideos, Channel $channel): void
    {
        is_null($channel->last_checked_at)
            ? $this->firstTimeImport($newVideos, $channel)
            : $this->insertNewVideosAndNotify($newVideos, $channel);
    }

    /**
     * Update the last_checked_at timestamp for a channel.
     *
     * @param  Channel  $channel  The channel to update.
     */
    private function updateChannelTimestamp(Channel $channel): void
    {
        $channel->updateLastChecked();
        Log::debug("Check for videos completed for channel: {$channel->name}.");
    }

    /**
     * Log a first-time import.
     *
     * @param  Channel  $channel  The channel for which the import was performed.
     * @param  array  $newVideos  An array of new videos.
     */
    private function logFirstTimeImport(Channel $channel, array $newVideos): void
    {
        Log::info("First-time import for channel: {$channel->name} completed with ".count($newVideos).' videos.');
    }

    /**
     * Insert videos without sending notifications.
     *
     * @param  array  $newVideos  An array of new videos to insert.
     * @param  Channel  $channel  The channel to which the videos belong.
     */
    private function insertVideosWithoutNotification(array $newVideos, Channel $channel): void
    {
        foreach ($newVideos as $newVideo) {
            $video = Video::create($newVideo);
            Log::info("New video added (notifications suppressed - channel muted): {$video->title} ({$video->video_id}) for channel: {$channel->name}.");
        }
    }

    /**
     * Insert videos and send notifications.
     *
     * @param  array  $newVideos  An array of new videos to insert.
     * @param  Channel  $channel  The channel to which the videos belong.
     */
    private function insertVideosWithNotification(array $newVideos, Channel $channel): void
    {
        $sendDiscordNotificationAction = app(SendDiscordNotificationAction::class);

        foreach ($newVideos as $newVideo) {
            $video = Video::create($newVideo);

            $this->sendEmailNotification($video, $channel);
            $this->sendDiscordNotification($video, $sendDiscordNotificationAction);

            Log::info("New video added and notifications sent: {$video->title} ({$video->video_id}) for channel: {$channel->name}.");
        }
    }

    /**
     * Send an email notification for a new video.
     *
     * @param  Video  $video  The new video.
     * @param  Channel  $channel  The channel to which the video belongs.
     */
    private function sendEmailNotification(Video $video, Channel $channel): void
    {
        Mail::to(Config::get('app.alert_emails'))->send(new NewVideoMail($video, $channel));
    }

    /**
     * Send a Discord notification for a new video if Discord is configured.
     *
     * @param  Video  $video  The new video.
     * @param  SendDiscordNotificationAction  $sendDiscordNotificationAction  The action to use for sending the notification.
     */
    private function sendDiscordNotification(Video $video, SendDiscordNotificationAction $sendDiscordNotificationAction): void
    {
        if (Config::get('app.discord_webhook_url')) {
            $sendDiscordNotificationAction->execute($video);
        }
    }
}
