<?php

declare(strict_types=1);

namespace App\Actions\YouTube;

use App\Models\Channel;
use App\Models\Video;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use SimpleXMLElement;

/**
 * Class ExtractVideos
 *
 * This action extracts and filters videos from RSS data.
 */
class ExtractVideos
{
    /**
     * Extracts new videos from the RSS feed data.
     *
     * @param  SimpleXMLElement  $rssData  The RSS feed data.
     * @param  Channel  $channel  The channel to which the videos belong.
     * @return array<int, array<string, mixed>> An array of new videos.
     */
    public function execute(SimpleXMLElement $rssData, Channel $channel): array
    {
        $existingVideoIds = $this->getExistingVideoIds($channel);
        $excludedWords = Config::get('excluded-video-words.skip_terms', []);
        $newVideos = [];

        if (! isset($rssData->entry) || count($rssData->entry) === 0) {
            return [];
        }

        foreach ($rssData->entry as $entry) {
            $videoId = $this->extractVideoId($entry);
            $title = (string) $entry->title;
            $description = $this->extractVideoDescription($entry);

            if (in_array($videoId, $existingVideoIds, true)) {
                Log::debug("Skipping existing video: {$title} ({$videoId})");

                continue;
            }

            if ($this->containsExcludedWords($title, $excludedWords)) {
                continue;
            }

            $newVideos[] = $this->createVideoData($entry, $channel, $videoId, $title, $description);
        }

        if (empty($newVideos)) {
            Log::info("No new valid videos found after filtering for channel: {$channel->name}.");
        }

        return $newVideos;
    }

    /**
     * Get existing video IDs for a channel.
     *
     * @param  Channel  $channel  The channel for which to get existing video IDs.
     * @return array<int, string> An array of existing video IDs.
     */
    private function getExistingVideoIds(Channel $channel): array
    {
        return Video::where('channel_id', $channel->id)->pluck('video_id')->toArray();
    }

    /**
     * Extract a video ID from an RSS entry.
     *
     * @param  SimpleXMLElement  $entry  The RSS entry.
     * @return string The extracted video ID.
     */
    private function extractVideoId(SimpleXMLElement $entry): string
    {
        return str_replace('yt:video:', '', (string) $entry->id);
    }

    /**
     * Extract the video description from the nested RSS data.
     */
    private function extractVideoDescription(SimpleXMLElement $entry): ?string
    {
        $MAX_TEXT_LENGTH = 10000; // 10k characters

        $namespaces = $entry->getNamespaces(true);

        if (! isset($namespaces['media'])) {
            return null;
        }

        $mediaGroup = $entry->children($namespaces['media'])->group;

        if (! $mediaGroup || ! isset($mediaGroup->description)) {
            return null;
        }

        $description = (string) $mediaGroup->description;

        if (strlen($description) > $MAX_TEXT_LENGTH) {
            return substr($description, 0, $MAX_TEXT_LENGTH);
        }

        return $description;
    }

    /**
     * Check if a video title contains any excluded words.
     *
     * @param  string  $title  The title to check.
     * @param  array<int|string, string>  $excludedWords  An array of excluded words.
     * @return bool True if the title contains excluded words, false otherwise.
     */
    private function containsExcludedWords(string $title, array $excludedWords): bool
    {
        foreach ($excludedWords as $excludedWord) {
            if (stripos($title, (string) $excludedWord) !== false) {
                Log::debug("Skipping video with excluded term '{$excludedWord}': {$title}");

                return true;
            }
        }

        return false;
    }

    /**
     * Create video data from an RSS entry.
     *
     * @param  SimpleXMLElement  $entry  The RSS entry.
     * @param  Channel  $channel  The channel to which the video belongs.
     * @param  string  $videoId  The ID of the video.
     * @param  string  $title  The title of the video.
     * @return array<string, mixed> The created video data.
     */
    private function createVideoData(SimpleXMLElement $entry, Channel $channel, string $videoId, string $title, ?string $description): array
    {
        return [
            'video_id' => $videoId,
            'title' => $title,
            'description' => $description,
            'published_at' => Carbon::parse((string) $entry->published)->utc(),
            'channel_id' => $channel->id,
            'created_at' => Carbon::now()->utc(),
            'updated_at' => Carbon::now()->utc(),
        ];
    }
}
