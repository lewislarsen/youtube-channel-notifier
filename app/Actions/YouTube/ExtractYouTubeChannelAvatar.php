<?php

declare(strict_types=1);

namespace App\Actions\YouTube;

use App\Models\Channel;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Symfony\Component\DomCrawler\Crawler;

class ExtractYouTubeChannelAvatar
{
    public function execute(Channel $channel): string
    {
        $channelUrl = $channel->getChannelUrl();

        $html = $this->fetchChannelPage($channelUrl);
        $avatarUrl = $this->extractImageSrcUrl($html);

        if (! $avatarUrl) {
            throw new RuntimeException('Could not extract avatar image URL');
        }

        return $avatarUrl;
    }

    protected function fetchChannelPage(string $url): string
    {
        $userAgents = [
            'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)',
            'Mozilla/5.0 (iPhone; CPU iPhone OS 14_7_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.1.2 Mobile/15E148 Safari/604.1',
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
        ];

        foreach ($userAgents as $userAgent) {
            $headers = [
                'User-Agent' => $userAgent,
                'Accept-Language' => 'en-US,en;q=0.9',
                'Accept' => 'text/html,application/xhtml+xml',
                'Cookie' => 'CONSENT=YES+cb; YSC=DwKYllHNwuw; VISITOR_INFO1_LIVE=C8lZLZhI35A;',
            ];

            try {
                $response = Http::withHeaders($headers)
                    ->withOptions(['timeout' => 30])
                    ->get($url);

                if ($response->failed()) {
                    continue;
                }

                $html = $response->body();
                if (! str_contains($html, 'consent.youtube.com')) {
                    return $html;
                }
            } catch (Exception $e) {
                Log::error($e->getMessage());
            }
        }

        throw new RuntimeException('Could not fetch channel page using any available method');
    }

    protected function extractImageSrcUrl(string $html): ?string
    {
        $crawler = new Crawler($html);
        $linkNode = $crawler->filterXPath('//link[@rel="image_src"]');

        if ($linkNode->count() > 0) {
            return $linkNode->attr('href');
        }

        return null;
    }
}
