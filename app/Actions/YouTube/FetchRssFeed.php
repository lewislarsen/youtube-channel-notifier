<?php

declare(strict_types=1);

namespace App\Actions\YouTube;

use App\Models\Channel;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use SimpleXMLElement;

/**
 * Class FetchRssFeed
 *
 * This action fetches and validates the RSS feed for a YouTube channel.
 */
class FetchRssFeed
{
    /**
     * Fetches the RSS feed for a given channel.
     *
     * @param  Channel  $channel  The channel for which to fetch the RSS feed.
     * @return SimpleXMLElement|null The RSS feed data or null if the fetch failed or no videos found.
     */
    public function execute(Channel $channel): ?SimpleXMLElement
    {
        $rssUrl = $this->buildRssUrl($channel);
        $response = Http::get($rssUrl);

        if ($response->failed()) {
            Log::error("Failed to fetch RSS feed for channel: {$channel->name}. Response: {$response->body()}");

            return null;
        }

        $rssData = simplexml_load_string($response->body());

        if ($rssData === false || $this->isInvalidRssData($rssData)) {
            Log::info("No videos found in RSS feed for channel: {$channel->name}.");

            return null;
        }

        return $rssData;
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
     * Check if the RSS data is invalid.
     *
     * @param  SimpleXMLElement|false  $rssData  The RSS data to check.
     * @return bool True if the data is invalid, false otherwise.
     */
    private function isInvalidRssData($rssData): bool
    {
        return $rssData === false || ! isset($rssData->entry);
    }
}
