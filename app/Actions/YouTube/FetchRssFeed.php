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
        return $this->transformYouTubeFeedUrl(sprintf(
            'https://www.youtube.com/feeds/videos.xml?channel_id=%s',
            $channel->channel_id
        ));
    }

    /**
     * Transform YouTube channel feeds to the uploads playlist feed that excludes Shorts.
     *
     * @param  string  $url  The feed URL to transform.
     * @return string The transformed URL, or the original URL when it is not transformable.
     */
    private function transformYouTubeFeedUrl(string $url): string
    {
        $parts = parse_url($url);

        if ($parts === false || ! isset($parts['host'], $parts['path'], $parts['query'])) {
            return $url;
        }

        $host = strtolower($parts['host']);

        if (! ($host === 'youtube.com' || str_ends_with($host, '.youtube.com'))) {
            return $url;
        }

        if ($parts['path'] !== '/feeds/videos.xml') {
            return $url;
        }

        parse_str($parts['query'], $query);

        if (
            ! isset($query['channel_id'])
            || ! is_string($query['channel_id'])
            || ! str_starts_with($query['channel_id'], 'UC')
            || strlen($query['channel_id']) <= 2
        ) {
            return $url;
        }

        $query['playlist_id'] = 'UULF'.substr($query['channel_id'], 2);
        unset($query['channel_id']);

        $scheme = $parts['scheme'] ?? 'https';
        $port = isset($parts['port']) ? ':'.$parts['port'] : '';
        $user = $parts['user'] ?? null;
        $pass = isset($parts['pass']) ? ':'.$parts['pass'] : '';
        $auth = $user !== null ? $user.$pass.'@' : '';
        $fragment = isset($parts['fragment']) ? '#'.$parts['fragment'] : '';

        return sprintf(
            '%s://%s%s%s%s?%s%s',
            $scheme,
            $auth,
            $parts['host'],
            $port,
            $parts['path'],
            http_build_query($query),
            $fragment
        );
    }

    /**
     * Check if the RSS data is invalid.
     *
     * @param  SimpleXMLElement|false  $rssData  The RSS data to check.
     * @return bool True if the data is invalid, false otherwise.
     */
    private function isInvalidRssData(SimpleXMLElement|false $rssData): bool
    {
        return $rssData === false || ! isset($rssData->entry);
    }
}
