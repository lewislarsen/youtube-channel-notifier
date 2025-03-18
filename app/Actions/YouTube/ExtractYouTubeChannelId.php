<?php

declare(strict_types=1);

namespace App\Actions\YouTube;

use Exception;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RuntimeException;
use Symfony\Component\DomCrawler\Crawler;

class ExtractYouTubeChannelId
{
    /**
     * Whether to run in debug mode.
     */
    protected bool $debugMode = false;

    /**
     * Execute the action.
     */
    public function execute(string $channelUrl, bool $debugMode = false): string
    {
        $this->debugMode = $debugMode;
        $formattedUrl = $this->formatChannelUrl($channelUrl);

        $this->debug("Formatted URL: {$formattedUrl}");

        return $this->extractChannelIdWithMultipleAttempts($formattedUrl);
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
     * Try multiple methods to extract the channel ID.
     */
    protected function extractChannelIdWithMultipleAttempts(string $url): string
    {
        $userAgents = [
            // Googlebot user agent (most effective at bypassing consent)
            'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)',
            // Mobile user agent
            'Mozilla/5.0 (iPhone; CPU iPhone OS 14_7_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.1.2 Mobile/15E148 Safari/604.1',
            // Desktop user agent with specific country
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
        ];

        $this->debug('Attempting channel ID extraction with '.count($userAgents).' different user agents');

        foreach ($userAgents as $index => $userAgent) {
            $this->debug('Attempt #'.($index + 1).' with user agent: '.$this->truncateUserAgent($userAgent));

            $headers = [
                'User-Agent' => $userAgent,
                'Accept-Language' => 'en-US,en;q=0.9',
                'Accept' => 'text/html,application/xhtml+xml',
                'Cookie' => 'CONSENT=YES+cb; YSC=DwKYllHNwuw; VISITOR_INFO1_LIVE=C8lZLZhI35A;',
            ];

            try {
                $html = $this->fetchPageWithCustomHeaders($url, $headers);

                if (str_contains($html, 'consent.youtube.com')) {
                    $this->debug('Received consent page, skipping this attempt');

                    continue;
                }

                $channelId = $this->extractChannelIdFromHtml($html);
                if ($channelId) {
                    $this->debug("Successfully extracted channel ID: {$channelId}");

                    return $channelId;
                }
            } catch (Exception $e) {
                $this->warn('Attempt failed: '.$e->getMessage());
            }
        }

        throw new RuntimeException('Could not extract channel ID using any available method');
    }

    /**
     * Fetch a web page with custom headers.
     *
     * @param  array<string, mixed>  $headers
     *
     * @throws ConnectionException
     */
    protected function fetchPageWithCustomHeaders(string $url, array $headers): string
    {
        $this->debug("Fetching URL: {$url}");

        $response = Http::withHeaders($headers)
            ->withOptions([
                'timeout' => 30,
            ])
            ->get($url);

        if ($response->failed()) {
            $errorMessage = "Failed to fetch the page. Status: {$response->status()}";
            $this->warn($errorMessage);
            throw new RuntimeException($errorMessage);
        }

        $this->debug('Successfully fetched page, response length: '.strlen($response->body()).' bytes');

        return $response->body();
    }

    /**
     * Extract channel ID from HTML content using multiple methods.
     */
    protected function extractChannelIdFromHtml(string $html): ?string
    {
        $this->debug('Attempting to extract channel ID from HTML');

        // Method 1: Try direct regex extraction
        $channelId = $this->extractChannelIdWithRegex($html);
        if ($channelId) {
            $this->debug("Successfully extracted channel ID with regex: {$channelId}");

            return $channelId;
        }

        // Method 2: Try DOM crawler
        try {
            $channelId = $this->extractChannelIdWithDomCrawler($html);
            if ($channelId) {
                $this->debug("Successfully extracted channel ID with DOM crawler: {$channelId}");

                return $channelId;
            }
        } catch (RuntimeException $e) {
            $this->warn('DOM crawler extraction failed: '.$e->getMessage());
        }

        $this->debug('Failed to extract channel ID from HTML');

        return null;
    }

    /**
     * Extract channel ID using regular expressions.
     */
    protected function extractChannelIdWithRegex(string $html): ?string
    {
        $this->debug('Attempting regex-based channel ID extraction');

        // Common channel ID patterns
        $patterns = [
            // iOS app link pattern
            '~ios-app://544007664/[^"]+?/channel/([a-zA-Z0-9_-]{24})~' => 'iOS app link',
            // JSON-LD pattern
            '~"channelId"\s*:\s*"([a-zA-Z0-9_-]{24})"~' => 'JSON-LD data',
            // Meta tag pattern
            '~<meta[^>]+content="[^"]*?channel_id=([a-zA-Z0-9_-]{24})[^"]*?"~i' => 'Meta tag',
            // Canonical link pattern for channel
            '~<link[^>]+rel="canonical"[^>]+href="https://www\.youtube\.com/channel/([a-zA-Z0-9_-]{24})"~i' => 'Canonical link',
            // Generic URL pattern
            '~youtube\.com/channel/([a-zA-Z0-9_-]{24})~' => 'URL pattern',
            // Another metadata pattern
            '~"externalId":"([a-zA-Z0-9_-]{24})"~' => 'External ID metadata',
            // Embed URL pattern
            '~embed/([a-zA-Z0-9_-]{24})~' => 'Embed URL',
            // Browser tab API pattern
            '~browseEndpoint":\{"browseId":"([a-zA-Z0-9_-]{24})~' => 'Browse endpoint',
            // Header renderer pattern
            '~"c4TabbedHeaderRenderer"[^}]+"channelId":"([a-zA-Z0-9_-]{24})"~' => 'Header renderer',
            // API URL pattern
            '~"webCommandMetadata"[^}]+"url":\s*"/channel/([a-zA-Z0-9_-]{24})"~' => 'API URL',
            // Video owner pattern
            '~"videoOwnerChannelId":"([a-zA-Z0-9_-]{24})"~' => 'Video owner',
            // Microformat pattern
            '~"microformat"[^}]+"externalId":"([a-zA-Z0-9_-]{24})"~' => 'Microformat',
        ];

        foreach ($patterns as $pattern => $description) {
            if (preg_match($pattern, $html, $matches)) {
                $this->debug("Found channel ID via {$description} pattern: {$matches[1]}");

                return $matches[1];
            }
        }

        $this->debug('No channel ID found with any regex pattern');

        return null;
    }

    /**
     * Extract the channel ID using Symfony's DomCrawler.
     */
    protected function extractChannelIdWithDomCrawler(string $html): ?string
    {
        $this->debug('Attempting DOM crawler-based channel ID extraction');

        $crawler = new Crawler($html);

        // Method 1: iOS app link
        $channelId = $this->extractFromIosAppLink($crawler);
        if ($channelId) {
            return $channelId;
        }

        // Method 2: Canonical link
        $channelId = $this->extractFromCanonicalLink($crawler);
        if ($channelId) {
            return $channelId;
        }

        // Method 3: Meta tags
        $channelId = $this->extractFromMetaTags($crawler);
        if ($channelId) {
            return $channelId;
        }

        // Method 4: JSON-LD scripts
        $channelId = $this->extractFromJsonLdScripts($crawler);
        if ($channelId) {
            return $channelId;
        }

        // Method 5: YouTube API initialization data
        $channelId = $this->extractFromYtInitialData($crawler);
        if ($channelId) {
            return $channelId;
        }

        if ($this->debugMode) {
            $this->saveHtmlForDebug($html, $crawler);
        }

        return null;
    }

    /**
     * Extract channel ID from iOS app link.
     */
    protected function extractFromIosAppLink(Crawler $crawler): ?string
    {
        $linkNode = $crawler->filterXPath('//head/link[@rel="alternate"][contains(@href, "ios-app://544007664/vnd.youtube")]');

        $this->debug("iOS app link count: {$linkNode->count()}");

        if ($linkNode->count() > 0) {
            $href = $linkNode->attr('href');
            $this->debug("iOS app link: {$href}");

            if (preg_match('#/channel/([^"&?/]+)#', (string) $href, $matches)) {
                $this->debug("Extracted channel ID from iOS app link: {$matches[1]}");

                return $matches[1];
            }
        }

        return null;
    }

    /**
     * Extract channel ID from canonical link.
     */
    protected function extractFromCanonicalLink(Crawler $crawler): ?string
    {
        $canonicalNode = $crawler->filterXPath('//head/link[@rel="canonical"]');

        $this->debug("Canonical link count: {$canonicalNode->count()}");

        if ($canonicalNode->count() > 0) {
            $href = $canonicalNode->attr('href');
            $this->debug("Canonical link: {$href}");

            if (preg_match('#/channel/([^"&?/]+)#', (string) $href, $matches)) {
                $this->debug("Extracted channel ID from canonical link: {$matches[1]}");

                return $matches[1];
            }
        }

        return null;
    }

    /**
     * Extract channel ID from meta tags.
     */
    protected function extractFromMetaTags(Crawler $crawler): ?string
    {
        $metaNodes = $crawler->filterXPath('//meta[contains(@content, "channel_id")]');

        $this->debug("Meta tags with channel_id count: {$metaNodes->count()}");

        foreach ($metaNodes as $metumNode) {
            // @phpstan-ignore-next-line
            $content = $metumNode->getAttribute('content');
            $this->debug("Meta content: {$content}");

            if (preg_match('#channel_id=([^"&?/]+)#', $content, $matches)) {
                $this->debug("Extracted channel ID from meta tag: {$matches[1]}");

                return $matches[1];
            }
        }

        return null;
    }

    /**
     * Extract channel ID from JSON-LD scripts.
     */
    protected function extractFromJsonLdScripts(Crawler $crawler): ?string
    {
        $scriptNodes = $crawler->filterXPath('//script[@type="application/ld+json"]');

        $this->debug("JSON-LD script count: {$scriptNodes->count()}");

        foreach ($scriptNodes as $scriptNode) {
            try {
                $jsonContent = $scriptNode->textContent;
                $data = json_decode($jsonContent, true, 512, JSON_THROW_ON_ERROR);

                $this->debug('JSON-LD data keys: '.(is_array($data) ? implode(', ', array_keys($data)) : 'invalid data'));

                if (is_array($data)) {
                    if (isset($data['channelId'])) {
                        $this->debug("Found channel ID in JSON-LD data: {$data['channelId']}");

                        return $data['channelId'];
                    }

                    if (isset($data['author']['identifier'])) {
                        $this->debug("Found channel ID in JSON-LD author data: {$data['author']['identifier']}");

                        return $data['author']['identifier'];
                    }
                }
            } catch (Exception $e) {
                $this->warn('JSON-LD parsing error: '.$e->getMessage());
            }
        }

        return null;
    }

    /**
     * Extract channel ID from YouTube initial data.
     */
    protected function extractFromYtInitialData(Crawler $crawler): ?string
    {
        $ytInitialDataNodes = $crawler->filterXPath('//script[contains(text(), "ytInitialData")]');

        $this->debug("ytInitialData script count: {$ytInitialDataNodes->count()}");

        foreach ($ytInitialDataNodes as $ytInitialDataNode) {
            try {
                $scriptContent = $ytInitialDataNode->textContent;
                if (preg_match('/var ytInitialData = (.+?});/', $scriptContent, $matches)) {
                    $jsonData = json_decode($matches[1], true, 512, JSON_THROW_ON_ERROR);

                    if (is_array($jsonData)) {
                        if (isset($jsonData['header']['c4TabbedHeaderRenderer']['channelId'])) {
                            $channelId = $jsonData['header']['c4TabbedHeaderRenderer']['channelId'];
                            $this->debug("Found channel ID in ytInitialData header: {$channelId}");

                            return $channelId;
                        }

                        $channelId = $this->findKeyInArray('channelId', $jsonData);
                        if ($channelId) {
                            $this->debug("Found channel ID in ytInitialData via recursive search: {$channelId}");

                            return $channelId;
                        }
                    }
                }
            } catch (Exception $e) {
                $this->warn('ytInitialData parsing error: '.$e->getMessage());
            }
        }

        return null;
    }

    /**
     * Save HTML to a file for debugging purposes.
     */
    protected function saveHtmlForDebug(string $html, Crawler $crawler): void
    {
        $filename = storage_path('logs/youtube_channel_page_'.\Carbon\Carbon::now()->getTimestamp().'.html');
        file_put_contents($filename, $html);
        $this->debug("Saved HTML to file for inspection: {$filename}");

        $this->debug('All link tags in head:');
        $allLinks = $crawler->filterXPath('//head/link');
        $allLinks->each(function (Crawler $crawler): void {
            $rel = $crawler->attr('rel') ?? 'no-rel';
            $href = $crawler->attr('href') ?? 'no-href';
            $this->debug("Link rel=\"{$rel}\" href=\"{$href}\"");
        });
    }

    /**
     * Recursively find a key in a nested array.
     *
     * @param  array<string, mixed>  $haystack
     */
    protected function findKeyInArray(string $needle, array $haystack): ?string
    {
        if (isset($haystack[$needle]) && is_string($haystack[$needle])) {
            return $haystack[$needle];
        }

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

    /**
     * Truncate user agent for cleaner debug logs.
     */
    protected function truncateUserAgent(string $userAgent): string
    {
        return Str::limit($userAgent, 30);
    }

    /**
     * Log debug message.
     */
    protected function debug(string $message): void
    {
        if ($this->debugMode) {
            Log::debug("[YouTubeExtractor] {$message}");
        }
    }

    /**
     * Log warning message.
     */
    protected function warn(string $message): void
    {
        if ($this->debugMode) {
            Log::warning("[YouTubeExtractor] {$message}");
        }
    }
}
