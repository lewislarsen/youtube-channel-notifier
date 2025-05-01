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
            ->and((string) $result->entry->title)->toBe('New Video Title');
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

    it('correctly builds the RSS feed URL for a channel', function (): void {
        $channel = Channel::factory()->create([
            'channel_id' => 'UC_x5XG1OV2P6uZZ5FSM9Ttw',
        ]);

        Http::fake([
            'https://www.youtube.com/feeds/videos.xml?channel_id=UC_x5XG1OV2P6uZZ5FSM9Ttw' => Http::response('<feed></feed>', 200),
        ]);

        $action = new FetchRssFeed;
        $action->execute($channel);

        Http::assertSent(function ($request) {
            return $request->url() === 'https://www.youtube.com/feeds/videos.xml?channel_id=UC_x5XG1OV2P6uZZ5FSM9Ttw';
        });
    });
});
