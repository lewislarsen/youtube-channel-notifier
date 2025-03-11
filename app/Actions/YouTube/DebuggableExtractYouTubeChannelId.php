<?php

declare(strict_types=1);

namespace App\Actions\YouTube;

use RuntimeException;
use Symfony\Component\DomCrawler\Crawler;

class DebuggableExtractYouTubeChannelId extends ExtractYouTubeChannelId
{
    /**
     * Debug callback function.
     *
     * @var callable|null
     */
    private $debugCallback = null;

    /**
     * Set the debug callback function.
     */
    public function setDebugCallback(callable $callback): self
    {
        $this->debugCallback = $callback;

        return $this;
    }

    /**
     * Log debug message.
     */
    protected function debug(string $message): void
    {
        if ($this->debugCallback) {
            call_user_func($this->debugCallback, $message);
        }
    }

    /**
     * Log warning message.
     */
    protected function warn(string $message): void
    {
        if ($this->debugCallback) {
            call_user_func($this->debugCallback, $message, 'warn');
        }
    }

    /**
     * Execute the action.
     */
    public function execute(string $channelUrl, bool $isDebugMode = false): string
    {
        $formattedUrl = $this->formatChannelUrl($channelUrl);

        if ($isDebugMode) {
            $this->debug("Formatted URL: {$formattedUrl}");
        }

        // Use specialized headers approach to bypass consent
        return $this->extractChannelIdWithSpecializedHeaders($formattedUrl, $isDebugMode);
    }

    /**
     * Try to extract channel ID with specialized headers.
     */
    protected function extractChannelIdWithSpecializedHeaders(string $url, bool $isDebugMode): string
    {
        $userAgents = [
            // Googlebot user agent (most effective at bypassing consent)
            'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)',
            'Mozilla/5.0 (iPhone; CPU iPhone OS 14_7_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.1.2 Mobile/15E148 Safari/604.1',
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
        ];

        foreach ($userAgents as $userAgent) {
            if ($isDebugMode) {
                $this->debug("Trying with User-Agent: {$userAgent}");
            }

            $html = $this->fetchPageWithCustomHeaders($url, [
                'User-Agent' => $userAgent,
                'Accept-Language' => 'en-US,en;q=0.9',
                'Accept' => 'text/html,application/xhtml+xml',
                // Set cookie for consent
                'Cookie' => 'CONSENT=YES+cb; YSC=DwKYllHNwuw; VISITOR_INFO1_LIVE=C8lZLZhI35A;',
            ]);

            // Check if we got a consent page
            if (str_contains($html, 'consent.youtube.com')) {
                if ($isDebugMode) {
                    $this->warn("Still hitting consent page with {$userAgent}");
                }

                continue;
            }

            // Try extraction
            $channelId = $this->extractChannelIdFromHtml($html, $isDebugMode);
            if ($channelId) {
                return $channelId;
            }
        }

        throw new RuntimeException('Could not extract channel ID using any available method');
    }

    /**
     * Master method to try all channel ID extraction methods.
     */
    protected function extractChannelIdFromHtml(string $html, bool $isDebugMode): ?string
    {
        // Method 1: Try direct regex extraction
        $channelId = $this->extractChannelIdWithRegex($html, $isDebugMode);
        if ($channelId) {
            return $channelId;
        }

        // Method 2: Try DOM crawler
        try {
            return $this->extractChannelIdWithDomCrawler($html, $isDebugMode);
        } catch (RuntimeException $e) {
            if ($isDebugMode) {
                $this->warn('DOM crawler extraction failed: '.$e->getMessage());
            }

            return null;
        }
    }

    /**
     * Extract channel ID using regular expressions.
     */
    protected function extractChannelIdWithRegex(string $html, bool $isDebugMode = false): ?string
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

        foreach ($patterns as $index => $pattern) {
            if (preg_match($pattern, $html, $matches)) {
                if ($isDebugMode) {
                    $this->debug("Found channel ID using regex pattern #{$index}: {$matches[1]}");
                }

                return $matches[1];
            }
        }

        return null;
    }

    /**
     * Extract the channel ID using Symfony's DomCrawler.
     */
    protected function extractChannelIdWithDomCrawler(string $html, bool $isDebugMode = false): string
    {
        $crawler = new Crawler($html);

        // Method 1: iOS app link
        $linkNode = $crawler->filterXPath('//head/link[@rel="alternate"][contains(@href, "ios-app://544007664/vnd.youtube")]');

        if ($isDebugMode) {
            $this->debug("iOS app link count: {$linkNode->count()}");
            if ($linkNode->count() > 0) {
                $this->debug("iOS app link: {$linkNode->attr('href')}");
            }
        }

        if ($linkNode->count() > 0) {
            $href = $linkNode->attr('href');
            if (preg_match('#/channel/([^"&?/]+)#', (string) $href, $matches)) {
                return $matches[1];
            }
        }

        // Method 2: Check for canonical link
        $canonicalNode = $crawler->filterXPath('//head/link[@rel="canonical"]');

        if ($isDebugMode) {
            $this->debug("Canonical link count: {$canonicalNode->count()}");
            if ($canonicalNode->count() > 0) {
                $this->debug("Canonical link: {$canonicalNode->attr('href')}");
            }
        }

        if ($canonicalNode->count() > 0) {
            $href = $canonicalNode->attr('href');
            if (preg_match('#/channel/([^"&?/]+)#', (string) $href, $matches)) {
                return $matches[1];
            }
        }

        // Method 3: Check for meta tag with channel ID
        $metaNodes = $crawler->filterXPath('//meta[contains(@content, "channel_id")]');

        if ($isDebugMode) {
            $this->debug("Meta tags with channel_id count: {$metaNodes->count()}");
            $metaNodes->each(function (Crawler $crawler): void {
                $this->debug("Meta content: {$crawler->attr('content')}");
            });
        }

        foreach ($metaNodes as $node) {
            // @phpstan-ignore-next-line
            $content = $node->getAttribute('content');
            if (preg_match('#channel_id=([^"&?/]+)#', $content, $matches)) {
                return $matches[1];
            }
        }

        // Method 4: Look for data in JSON-LD scripts
        $scriptNodes = $crawler->filterXPath('//script[@type="application/ld+json"]');

        if ($isDebugMode) {
            $this->debug("JSON-LD script count: {$scriptNodes->count()}");
        }

        foreach ($scriptNodes as $scriptNode) {
            $jsonContent = $scriptNode->textContent;
            $data = json_decode($jsonContent, true, 512, JSON_THROW_ON_ERROR);

            if ($isDebugMode && json_last_error() !== JSON_ERROR_NONE) {
                $this->warn('JSON decode error: '.json_last_error_msg());
            }

            if (is_array($data)) {
                if ($isDebugMode) {
                    $this->debug('JSON-LD data keys: '.implode(', ', array_keys($data)));
                }

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

        if ($isDebugMode) {
            $this->debug("ytInitialData script count: {$ytInitialDataNodes->count()}");
        }

        foreach ($ytInitialDataNodes as $ytInitialDataNode) {
            $scriptContent = $ytInitialDataNode->textContent;
            if (preg_match('/var ytInitialData = (.+?});/', $scriptContent, $matches)) {
                $jsonData = json_decode($matches[1], true, 512, JSON_THROW_ON_ERROR);

                if ($isDebugMode && json_last_error() !== JSON_ERROR_NONE) {
                    $this->warn('ytInitialData JSON decode error: '.json_last_error_msg());
                }

                if (is_array($jsonData)) {
                    // Try to extract channel ID from YouTube initial data
                    if ($isDebugMode) {
                        $this->debugYtInitialData($jsonData);
                    }

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

        if ($isDebugMode) {
            // Save HTML to a file for manual inspection
            $filename = storage_path('logs/youtube_channel_page_'.\Carbon\Carbon::now()->getTimestamp().'.html');
            file_put_contents($filename, $html);
            $this->debug("Saved HTML to file for inspection: {$filename}");

            // Output all link tags for further inspection
            $this->debug('All link tags in head:');
            $allLinks = $crawler->filterXPath('//head/link');
            $allLinks->each(function (Crawler $crawler): void {
                $rel = $crawler->attr('rel') ?? 'no-rel';
                $href = $crawler->attr('href') ?? 'no-href';
                $this->debug("Link rel=\"{$rel}\" href=\"{$href}\"");
            });
        }

        throw new RuntimeException('Could not extract channel ID using any of the DOM crawler methods.');
    }

    /**
     * Debug YouTube initial data structure.
     */
    private function debugYtInitialData(array $data, string $prefix = ''): void
    {
        $keys = array_keys($data);
        $this->debug("{$prefix}Keys: ".implode(', ', $keys));

        // Look for likely candidates containing channel ID
        $importantKeys = ['channelId', 'id', 'externalId', 'channel', 'header', 'metadata'];

        foreach ($importantKeys as $importantKey) {
            if (isset($data[$importantKey])) {
                if (is_array($data[$importantKey])) {
                    $this->debug("{$prefix}Found important key: {$importantKey} (array)");
                    // Only go one level deeper to avoid too much output
                    if (strlen($prefix) < 3) {
                        $this->debugYtInitialData($data[$importantKey], $prefix.'  '.$importantKey.' > ');
                    }
                } else {
                    $this->debug("{$prefix}Found important key: {$importantKey} = {$data[$importantKey]}");
                }
            }
        }
    }
}
