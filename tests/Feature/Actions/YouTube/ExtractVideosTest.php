<?php

declare(strict_types=1);

use App\Actions\YouTube\ExtractVideos;
use App\Models\Channel;
use App\Models\ExcludedWord;
use App\Models\Video;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

beforeEach(function (): void {
    Channel::truncate();
    Video::truncate();
    ExcludedWord::truncate();
});

describe('ExtractVideos', function (): void {
    it('extracts video data from RSS feed entries', function (): void {
        $channel = Channel::factory()->create([
            'channel_id' => 'UC_x5XG1OV2P6uZZ5FSM9Ttw',
        ]);

        $rssXml = <<<'XML'
        <feed xmlns:media="http://search.yahoo.com/mrss/">
            <entry>
                <id>yt:video:5ltAy1W6k-Q</id>
                <title>New Video Title</title>
                <published>2025-01-01T00:00:00+00:00</published>
                <media:group>
                 <media:description>This is my video description!</media:description>
                </media:group>
            </entry>
        </feed>
        XML;

        $rssData = simplexml_load_string($rssXml);

        $action = new ExtractVideos;
        $newVideos = $action->execute($rssData, $channel);

        expect($newVideos)->toBeArray()
            ->and(count($newVideos))->toBe(1)
            ->and($newVideos[0]['video_id'])->toBe('5ltAy1W6k-Q')
            ->and($newVideos[0]['title'])->toBe('New Video Title')
            ->and($newVideos[0]['description'])->toBe('This is my video description!')
            ->and($newVideos[0]['channel_id'])->toBe($channel->id);
    });

    it('skips already existing videos', function (): void {
        $channel = Channel::factory()->create();

        // Create an existing video
        Video::create([
            'video_id' => '5ltAy1W6k-Q',
            'title' => 'Existing Video',
            'description' => 'Already in database',
            'published_at' => now(),
            'channel_id' => $channel->id,
        ]);

        $rssXml = <<<'XML'
        <feed>
            <entry>
                <id>yt:video:5ltAy1W6k-Q</id>
                <title>Existing Video With Updates</title>
                <summary>Updated description</summary>
                <published>2025-01-01T00:00:00+00:00</published>
            </entry>
            <entry>
                <id>yt:video:6mBgT3W7Ttw</id>
                <title>New Video</title>
                <summary>New description</summary>
                <published>2025-01-02T00:00:00+00:00</published>
            </entry>
        </feed>
        XML;

        $rssData = simplexml_load_string($rssXml);

        $action = new ExtractVideos;
        $newVideos = $action->execute($rssData, $channel);

        expect($newVideos)->toBeArray()
            ->and(count($newVideos))->toBe(1)
            ->and($newVideos[0]['video_id'])->toBe('6mBgT3W7Ttw')
            ->and($newVideos[0]['title'])->toBe('New Video');
    });

    it('filters out videos containing excluded words', function (): void {
        ExcludedWord::insert([
            ['word' => 'LIVE', 'created_at' => now(), 'updated_at' => now()],
            ['word' => 'Premiere', 'created_at' => now(), 'updated_at' => now()],
            ['word' => 'Trailer', 'created_at' => now(), 'updated_at' => now()],
        ]);

        $channel = Channel::factory()->create();

        $rssXml = <<<'XML'
        <feed>
            <entry>
                <id>yt:video:1aaaaaaaa</id>
                <title>LIVE Stream Event</title>
                <summary>This is a live stream</summary>
                <published>2025-01-01T00:00:00+00:00</published>
            </entry>
            <entry>
                <id>yt:video:2bbbbbbbbb</id>
                <title>Movie Trailer 2025</title>
                <summary>New movie trailer</summary>
                <published>2025-01-01T00:00:00+00:00</published>
            </entry>
            <entry>
                <id>yt:video:3ccccccccc</id>
                <title>Premiere of New Show</title>
                <summary>Premiering tonight</summary>
                <published>2025-01-01T00:00:00+00:00</published>
            </entry>
            <entry>
                <id>yt:video:4ddddddddd</id>
                <title>Normal Video</title>
                <summary>Regular content</summary>
                <published>2025-01-01T00:00:00+00:00</published>
            </entry>
        </feed>
        XML;

        $rssData = simplexml_load_string($rssXml);

        Log::shouldReceive('debug')->times(3);

        $action = new ExtractVideos;
        $newVideos = $action->execute($rssData, $channel);

        expect($newVideos)->toBeArray()
            ->and(count($newVideos))->toBe(1)
            ->and($newVideos[0]['video_id'])->toBe('4ddddddddd')
            ->and($newVideos[0]['title'])->toBe('Normal Video');
    });

    it('returns empty array when no new videos are found', function (): void {
        $channel = Channel::factory()->create();

        // Create an existing video
        Video::create([
            'video_id' => '5ltAy1W6k-Q',
            'title' => 'Existing Video',
            'description' => 'Already in database',
            'published_at' => now(),
            'channel_id' => $channel->id,
        ]);

        $rssXml = <<<'XML'
        <feed>
            <entry>
                <id>yt:video:5ltAy1W6k-Q</id>
                <title>Existing Video With Updates</title>
                <summary>Updated description</summary>
                <published>2025-01-01T00:00:00+00:00</published>
            </entry>
        </feed>
        XML;

        $rssData = simplexml_load_string($rssXml);

        Log::shouldReceive('info')->once();
        Log::shouldReceive('debug')->once();

        $action = new ExtractVideos;
        $newVideos = $action->execute($rssData, $channel);

        expect($newVideos)->toBeArray()
            ->and($newVideos)->toBeEmpty();
    });

    it('correctly parses published dates from feed', function (): void {
        $channel = Channel::factory()->create();

        $rssXml = <<<'XML'
    <feed>
        <entry>
            <id>yt:video:5ltAy1W6k-Q</id>
            <title>New Video Title</title>
            <summary>Video description</summary>
            <published>2025-01-15T13:45:30+00:00</published>
        </entry>
    </feed>
    XML;

        $rssData = simplexml_load_string($rssXml);

        $action = new ExtractVideos;
        $newVideos = $action->execute($rssData, $channel);

        expect($newVideos[0]['published_at'])->toBeInstanceOf(Carbon::class)
            ->and($newVideos[0]['published_at']->format('Y-m-d H:i:s'))->toBe('2025-01-15 13:45:30');
    });

    it('correctly extracts the description', function (): void {
        $channel = Channel::factory()->create();

        $rssXml = <<<'XML'
        <feed xmlns:media="http://search.yahoo.com/mrss/">
            <entry>
                <id>yt:video:5ltAy1W6k-Q</id>
                <title>New Video Title</title>
                <published>2025-01-01T00:00:00+00:00</published>
                <media:group>
                 <media:description>This is my video description!</media:description>
                </media:group>
            </entry>
        </feed>
        XML;

        $rssData = simplexml_load_string($rssXml);

        $action = new ExtractVideos;
        $newVideos = $action->execute($rssData, $channel);

        expect($newVideos[0]['description'])->toBe('This is my video description!');
    });

    it('truncates the description if it is over the specified character limit', function (): void {
        // this character limit is set to 10k in ExtractVideos
        $channel = Channel::factory()->create();
        $longDescription = str_repeat('a', 10001); // 10,001 characters

        $rssXml = <<<XML
  <feed xmlns:media="http://search.yahoo.com/mrss/">
            <entry>
                <id>yt:video:5ltAy1W6k-Q</id>
                <title>New Video Title</title>
                <published>2025-01-01T00:00:00+00:00</published>
                <media:group>
                <media:description>{$longDescription}</media:description>
                </media:group>
            </entry>
        </feed>
XML;

        $rssData = simplexml_load_string($rssXml);

        $action = new ExtractVideos;
        $newVideos = $action->execute($rssData, $channel);

        expect(strlen((string) $newVideos[0]['description']))->toBe(10000)
            ->and($newVideos[0]['description'])->toBe(substr($longDescription, 0, 10000));
    });

    it('handles a feed with no entries', function (): void {
        $channel = Channel::factory()->create();

        $rssXml = <<<'XML'
        <feed>
        </feed>
        XML;

        $rssData = simplexml_load_string($rssXml);

        $action = new ExtractVideos;
        $newVideos = $action->execute($rssData, $channel);

        expect($newVideos)->toBeArray()
            ->and($newVideos)->toBeEmpty();
    });
});
