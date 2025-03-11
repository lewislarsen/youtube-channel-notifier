<?php

declare(strict_types=1);

namespace App\Actions\YouTube;

use Illuminate\Support\Facades\Http;
use RuntimeException;
use Symfony\Component\DomCrawler\Crawler;

class ExtractYouTubeChannelId
{
    /**
     * Execute the action.
     */
    public function execute(string $channelUrl, bool $isDebugMode = false): string
    {
        $formattedUrl = $this->formatChannelUrl($channelUrl);

        return $this->extractChannelIdWithSpecializedHeaders($formattedUrl, $isDebugMode);
    }

    /**
     * Format the channel URL based on input format.
     */
    protected function formatChannelUrl(string $channelUrl): string
    {
        if (str_starts_with($channelUrl, '@')) {
            return 'https://www.youtube.com/'.$channelUrl;
        }

        if (! str_starts_with($channelUrl, 'http')) {
            return 'https://www.youtube.com/channel/'.$channelUrl;
        }

        return $channelUrl;
    }

    /**
     * Try to extract channel ID with specialized headers.
     */
    private function extractChannelIdWithSpecializedHeaders(string $url, bool $isDebugMode): string
    {
        // Try with multiple user agents and header combinations
        $userAgents = [
            // Googlebot user agent (most effective at bypassing consent)
            'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)',
            // Mobile user agent
            'Mozilla/5.0 (iPhone; CPU iPhone OS 14_7_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.1.2 Mobile/15E148 Safari/604.1',
            // Desktop user agent with specific country
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
        ];

        foreach ($userAgents as $userAgent) {
            $html = $this->fetchPageWithCustomHeaders($url, [
                'User-Agent' => $userAgent,
                'Accept-Language' => 'en-US,en;q=0.9',
                'Accept' => 'text/html,application/xhtml+xml',
                // Set cookie for consent
                'Cookie' => 'CONSENT=YES+cb; YSC=DwKYllHNwuw; VISITOR_INFO1_LIVE=C8lZLZhI35A;',
            ]);

            // Check if we got a consent page
            if (str_contains($html, 'consent.youtube.com')) {
                continue;
            }

            // Try extraction
            $channelId = $this->extractChannelIdFromHtml($html);
            if ($channelId) {
                return $channelId;
            }
        }

        throw new RuntimeException('Could not extract channel ID using any available method');
    }

    /**
     * Fetch a web page with custom headers.
     */
    protected function fetchPageWithCustomHeaders(string $url, array $headers): string
    {
        $response = Http::withHeaders($headers)
            ->withOptions([
                'timeout' => 30,
            ])
            ->get($url);

        if ($response->failed()) {
            throw new RuntimeException("Failed to fetch the page. Status: {$response->status()}");
        }

        return $response->body();
    }

    /**
     * Master method to try all channel ID extraction methods.
     */
    private function extractChannelIdFromHtml(string $html): ?string
    {
        // Method 1: Try direct regex extraction
        $channelId = $this->extractChannelIdWithRegex($html);
        if ($channelId) {
            return $channelId;
        }

        // Method 2: Try DOM crawler
        try {
            return $this->extractChannelIdWithDomCrawler($html);
        } catch (RuntimeException) {
            return null;
        }
    }

    /**
     * Extract channel ID using regular expressions.
     */
    private function extractChannelIdWithRegex(string $html): ?string
    {
        // Common channel ID patterns
        $patterns = [
            // iOS app link pattern
            '~ios-app://544007664/[^"]+?/channel/([a-zA-Z0-9_-]{24})~',

            // JSON-LD pattern
            '~"channelId"\s*:\s*"([a-zA-Z0-9_-]{24})"~',

            // Meta tag pattern
            '~<meta[^>]+content="[^"]*?channel_id=([a-zA-Z0-9_-]{24})[^"]*?"~i',

            // Canonical link pattern for channel
            '~<link[^>]+rel="canonical"[^>]+href="https://www\.youtube\.com/channel/([a-zA-Z0-9_-]{24})"~i',

            // Generic URL pattern
            '~youtube\.com/channel/([a-zA-Z0-9_-]{24})~',

            // Another metadata pattern
            '~"externalId":"([a-zA-Z0-9_-]{24})"~',

            // Embed URL pattern
            '~embed/([a-zA-Z0-9_-]{24})~',

            // Browser tab API pattern
            '~browseEndpoint":\{"browseId":"([a-zA-Z0-9_-]{24})~',

            // Header renderer pattern
            '~"c4TabbedHeaderRenderer"[^}]+"channelId":"([a-zA-Z0-9_-]{24})"~',

            // API URL pattern
            '~"webCommandMetadata"[^}]+"url":\s*"/channel/([a-zA-Z0-9_-]{24})"~',

            // Video owner pattern
            '~"videoOwnerChannelId":"([a-zA-Z0-9_-]{24})"~',

            // Microformat pattern
            '~"microformat"[^}]+"externalId":"([a-zA-Z0-9_-]{24})"~',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $html, $matches)) {
                return $matches[1];
            }
        }

        return null;
    }

    /**
     * Extract the channel ID using Symfony's DomCrawler.
     */
    private function extractChannelIdWithDomCrawler(string $html): string
    {
        $crawler = new Crawler($html);

        // Method 1: iOS app link
        $linkNode = $crawler->filterXPath('//head/link[@rel="alternate"][contains(@href, "ios-app://544007664/vnd.youtube")]');

        if ($linkNode->count() > 0) {
            $href = $linkNode->attr('href');
            if (preg_match('#/channel/([^"&?/]+)#', (string) $href, $matches)) {
                return $matches[1];
            }
        }

        // Method 2: Check for canonical link
        $canonicalNode = $crawler->filterXPath('//head/link[@rel="canonical"]');

        if ($canonicalNode->count() > 0) {
            $href = $canonicalNode->attr('href');
            if (preg_match('#/channel/([^"&?/]+)#', (string) $href, $matches)) {
                return $matches[1];
            }
        }

        // Method 3: Check for meta tag with channel ID
        $metaNodes = $crawler->filterXPath('//meta[contains(@content, "channel_id")]');

        foreach ($metaNodes as $node) {
            // @phpstan-ignore-next-line
            $content = $node->getAttribute('content');
            if (preg_match('#channel_id=([^"&?/]+)#', $content, $matches)) {
                return $matches[1];
            }
        }

        // Method 4: Look for data in JSON-LD scripts
        $scriptNodes = $crawler->filterXPath('//script[@type="application/ld+json"]');

        foreach ($scriptNodes as $scriptNode) {
            $jsonContent = $scriptNode->textContent;
            $data = json_decode($jsonContent, true, 512, JSON_THROW_ON_ERROR);

            if (is_array($data)) {
                // Check for channel ID in various possible locations in the JSON-LD
                if (isset($data['channelId'])) {
                    return $data['channelId'];
                }

                if (isset($data['author']['identifier'])) {
                    return $data['author']['identifier'];
                }
            }
        }

        // Method 5: Search for YouTube API initialization data
        $ytInitialDataNodes = $crawler->filterXPath('//script[contains(text(), "ytInitialData")]');

        foreach ($ytInitialDataNodes as $ytInitialDataNode) {
            $scriptContent = $ytInitialDataNode->textContent;
            if (preg_match('/var ytInitialData = (.+?});/', $scriptContent, $matches)) {
                $jsonData = json_decode($matches[1], true);

                if (is_array($jsonData)) {
                    // Example extraction logic - adjust based on actual structure
                    if (isset($jsonData['header']['c4TabbedHeaderRenderer']['channelId'])) {
                        return $jsonData['header']['c4TabbedHeaderRenderer']['channelId'];
                    }

                    // Alternatively, traverse the structure recursively to find channelId
                    $channelId = $this->findKeyInArray('channelId', $jsonData);
                    if ($channelId) {
                        return $channelId;
                    }
                }
            }
        }

        throw new RuntimeException('Could not extract channel ID using any of the DOM crawler methods.');
    }

    /**
     * Recursively find a key in a nested array.
     */
    protected function findKeyInArray(string $needle, array $haystack): ?string
    {
        // If the key exists directly
        if (isset($haystack[$needle]) && is_string($haystack[$needle])) {
            return $haystack[$needle];
        }

        // Search recursively
        foreach ($haystack as $key => $value) {
            if ($key === $needle && is_string($value)) {
                return $value;
            }

            if (is_array($value)) {
                $result = $this->findKeyInArray($needle, $value);
                if ($result) {
                    return $result;
                }
            }
        }

        return null;
    }
}
