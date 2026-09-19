<?php

declare(strict_types=1);

use App\Actions\YouTube\FetchRssFeed;
use App\Models\Channel;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

beforeEach(function (): void {
    Channel::truncate();
});

describe('FetchRssFeed', function (): void {
    it('successfully fetches an RSS feed and returns SimpleXMLElement', function (): void {
        $channel = Channel::factory()->create([
            'channel_id' => 'UC_x5XG1OV2P6uZZ5FSM9Ttw',
        ]);

        $rssResponse = <<<'XML'
        <feed>
            <entry>
                <id>yt:video:5ltAy1W6k-Q</id>
                <title>New Video Title</title>
                <summary>Video description</summary>
                <published>2025-01-01T00:00:00+00:00</published>
            </entry>
        </feed>
        XML;

        Http::fake([
            'https://www.youtube.com/feeds/videos.xml*' => Http::response($rssResponse, 200),
        ]);

        $action = new FetchRssFeed;
        $result = $action->execute($channel);

        expect($result)->toBeInstanceOf(SimpleXMLElement::class)
            ->and((string) $result->entry->id)->toBe('yt:video:5ltAy1W6k-Q')
            ->and((string) $result->entry->title)->toBe('New Video Title')
            ->and((string) $result->entry->summary)->toBe('Video description');
    });

    it('returns null when the response fails', function (): void {
        Log::shouldReceive('error')->once();

        $channel = Channel::factory()->create([
            'channel_id' => 'UC_x5XG1OV2P6uZZ5FSM9Ttw',
        ]);

        Http::fake([
            'https://www.youtube.com/feeds/videos.xml*' => Http::response(null, 500),
        ]);

        $action = new FetchRssFeed;
        $result = $action->execute($channel);

        expect($result)->toBeNull();
    });

    it('returns null when no videos are found in the feed', function (): void {
        Log::shouldReceive('info')->once();

        $channel = Channel::factory()->create([
            'channel_id' => 'UC_x5XG1OV2P6uZZ5FSM9Ttw',
        ]);

        $rssResponse = <<<'XML'
        <feed>
        </feed>
        XML;

        Http::fake([
            'https://www.youtube.com/feeds/videos.xml*' => Http::response($rssResponse, 200),
        ]);

        $action = new FetchRssFeed;
        $result = $action->execute($channel);

        expect($result)->toBeNull();
    });

    it('builds the RSS feed URL with the normal videos playlist for a channel', function (): void {
        $channel = Channel::factory()->create([
            'channel_id' => 'UC_x5XG1OV2P6uZZ5FSM9Ttw',
        ]);

        Http::fake([
            'https://www.youtube.com/feeds/videos.xml?playlist_id=UULF_x5XG1OV2P6uZZ5FSM9Ttw' => Http::response('<feed></feed>', 200),
        ]);

        $action = new FetchRssFeed;
        $action->execute($channel);

        Http::assertSent(function ($request) {
            return $request->url() === 'https://www.youtube.com/feeds/videos.xml?playlist_id=UULF_x5XG1OV2P6uZZ5FSM9Ttw';
        });
    });

    it('only transforms eligible YouTube channel feed URLs', function (string $input, string $expected): void {
        $action = new FetchRssFeed;
        $method = new ReflectionMethod($action, 'transformYouTubeFeedUrl');

        expect($method->invoke($action, $input))->toBe($expected);
    })->with([
        'youtube channel feed' => [
            'https://www.youtube.com/feeds/videos.xml?channel_id=UCxSGC9BtJN2jgcz7ynXWdOw',
            'https://www.youtube.com/feeds/videos.xml?playlist_id=UULFxSGC9BtJN2jgcz7ynXWdOw',
        ],
        'youtube channel feed with existing query parameters' => [
            'https://www.youtube.com/feeds/videos.xml?channel_id=UCxSGC9BtJN2jgcz7ynXWdOw&foo=bar',
            'https://www.youtube.com/feeds/videos.xml?foo=bar&playlist_id=UULFxSGC9BtJN2jgcz7ynXWdOw',
        ],
        'youtube subdomain feed' => [
            'https://music.youtube.com/feeds/videos.xml?channel_id=UCxSGC9BtJN2jgcz7ynXWdOw',
            'https://music.youtube.com/feeds/videos.xml?playlist_id=UULFxSGC9BtJN2jgcz7ynXWdOw',
        ],
        'already playlist feed' => [
            'https://www.youtube.com/feeds/videos.xml?playlist_id=UULFxSGC9BtJN2jgcz7ynXWdOw',
            'https://www.youtube.com/feeds/videos.xml?playlist_id=UULFxSGC9BtJN2jgcz7ynXWdOw',
        ],
        'malformed url' => [
            'https://www.youtube.com/feeds/videos.xml?channel_id',
            'https://www.youtube.com/feeds/videos.xml?channel_id',
        ],
        'empty UC channel ID' => [
            'https://www.youtube.com/feeds/videos.xml?channel_id=UC',
            'https://www.youtube.com/feeds/videos.xml?channel_id=UC',
        ],
        'youtube feed without UC channel ID' => [
            'https://www.youtube.com/feeds/videos.xml?channel_id=OLAK5uy_example',
            'https://www.youtube.com/feeds/videos.xml?channel_id=OLAK5uy_example',
        ],
        'youtube non-feed url' => [
            'https://www.youtube.com/watch?channel_id=UCxSGC9BtJN2jgcz7ynXWdOw',
            'https://www.youtube.com/watch?channel_id=UCxSGC9BtJN2jgcz7ynXWdOw',
        ],
        'non-youtube feed' => [
            'https://example.com/feeds/videos.xml?channel_id=UCxSGC9BtJN2jgcz7ynXWdOw',
            'https://example.com/feeds/videos.xml?channel_id=UCxSGC9BtJN2jgcz7ynXWdOw',
        ],
    ]);
});
