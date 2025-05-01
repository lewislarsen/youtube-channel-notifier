<?php

declare(strict_types=1);

use App\Actions\CheckForVideos;
use App\Mail\NewVideoMail;
use App\Models\Channel;
use App\Models\Video;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

beforeEach(function (): void {
    Channel::truncate();
    Video::truncate();
});

describe('Core Video Detection', function (): void {
    beforeEach(function (): void {
        Mail::fake();
    });

    it('does not send a mailable if no new videos are found', function (): void {
        $channel = Channel::factory()->create([
            'channel_id' => 'UC_x5XG1OV2P6uZZ5FSM9Ttw',
            'last_checked_at' => now()->subDay(),
        ]);

        $rssResponse = <<<'XML'
        <feed>
        </feed>
        XML;

        Http::fake([
            'https://www.youtube.com/feeds/videos.xml*' => Http::response($rssResponse, 200),
        ]);

        $action = new CheckForVideos;
        $action->execute($channel);

        Mail::assertNothingSent();
    });

    it('sends a mailable if a new video is found', function (): void {
        Config::set('app.alert_emails', 'email@example.com');

        $channel = Channel::factory()->create([
            'channel_id' => 'UC_x5XG1OV2P6uZZ5FSM9Ttw',
            'last_checked_at' => now()->subDay(),
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

        $action = new CheckForVideos;
        $action->execute($channel);

        Mail::assertSent(NewVideoMail::class, function ($mail) {
            return $mail->hasTo('email@example.com');
        });

        expect(Video::where('video_id', '5ltAy1W6k-Q')->exists())->toBeTrue();
    });

    it('does not send a mailable on first-time import', function (): void {
        $channel = Channel::factory()->create([
            'channel_id' => 'UC_x5XG1OV2P6uZZ5FSM9Ttw',
            'last_checked_at' => null,
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

        $action = new CheckForVideos;
        $action->execute($channel);

        Mail::assertNothingSent();

        expect(Video::where('video_id', '5ltAy1W6k-Q')->exists())->toBeTrue();
    });
});

describe('Filtering', function (): void {
    beforeEach(function (): void {
        Mail::fake();
    });

    it('ignores videos with titles containing words from the skipped terms config', function (): void {
        Config::set('excluded-video-words.skip_terms', [
            'LIVE',
            'Premiere',
            'Trailer',
        ]);

        $channel = Channel::factory()->create([
            'channel_id' => 'UC_x5XG1OV2P6uZZ5FSM9Ttw',
            'last_checked_at' => now()->subDay(),
        ]);

        $rssResponse = <<<'XML'
        <feed>
            <entry>
                <id>yt:video:5ltAy1W6k-Q</id>
                <title>LIVE Video Title</title>
                <summary>Video description</summary>
                <published>2025-01-01T00:00:00+00:00</published>
            </entry>
            <entry>
                <id>yt:video:6xxxx6W7Ttw</id>
                <title>Premiere Event</title>
                <summary>Another description</summary>
                <published>2025-01-02T00:00:00+00:00</published>
            </entry>
            <entry>
                <id>yt:video:7yyyy7X8Uuw</id>
                <title>Normal Video Title</title>
                <summary>Regular description</summary>
                <published>2025-01-03T00:00:00+00:00</published>
            </entry>
        </feed>
        XML;

        Http::fake([
            'https://www.youtube.com/feeds/videos.xml*' => Http::response($rssResponse, 200),
        ]);

        $action = new CheckForVideos;
        $action->execute($channel);

        Mail::assertSent(NewVideoMail::class, function ($mail) {
            $video = $mail->video;

            return $video->video_id === '7yyyy7X8Uuw' && $video->title === 'Normal Video Title';
        });

        $this->assertDatabaseMissing('videos', [
            'video_id' => '5ltAy1W6k-Q',
        ]);

        $this->assertDatabaseMissing('videos', [
            'video_id' => '6xxxx6W7Ttw',
        ]);

        $this->assertDatabaseHas('videos', [
            'video_id' => '7yyyy7X8Uuw',
        ]);
    });

    it('does not send any notifications if the channel has been muted', function (): void {
        Config::set('app.alert_emails', 'email@example.com');
        Config::set('app.discord_webhook_url', 'https://discord.com/api/webhooks/test');

        $channel = Channel::factory()->muted()->create([
            'channel_id' => 'UC_x5XG1OV2P6uZZ5FSM9Ttw',
            'last_checked_at' => now()->subDay(),
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

        $action = new CheckForVideos;
        $action->execute($channel);

        Mail::assertNotSent(NewVideoMail::class);

        Http::assertNotSent(function ($request) {
            return str_contains((string) $request->url(), 'discord.com/api/webhooks');
        });

        expect(Video::where('video_id', '5ltAy1W6k-Q')->exists())->toBeTrue()
            ->and($channel->isMuted())->toBeTrue();
    });
});

describe('Notifications', function (): void {
    describe('Email', function (): void {
        beforeEach(function (): void {
            Mail::fake();
        });

        it('sends notification to multiple email addresses when configured', function (): void {
            Config::set('app.alert_emails', ['email1@example.com', 'email2@example.com']);

            $channel = Channel::factory()->create([
                'channel_id' => 'UC_x5XG1OV2P6uZZ5FSM9Ttw',
                'last_checked_at' => now()->subDay(),
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

            $action = new CheckForVideos;
            $action->execute($channel);

            Mail::assertSent(NewVideoMail::class, function ($mail) {
                return $mail->hasTo('email1@example.com') &&
                    $mail->hasTo('email2@example.com');
            });

            expect(Video::where('video_id', '5ltAy1W6k-Q')->exists())->toBeTrue();
        });
    });

    describe('Discord', function (): void {
        beforeEach(function (): void {
            Mail::fake();
        });

        it('sends both email and discord notifications for new videos', function (): void {
            Config::set('app.alert_emails', 'email@example.com');
            Config::set('app.discord_webhook_url', 'https://discord.com/api/webhooks/test');

            $channel = Channel::factory()->create([
                'channel_id' => 'UC_x5XG1OV2P6uZZ5FSM9Ttw',
                'last_checked_at' => now()->subDay(),
            ]);

            Http::fake([
                'https://www.youtube.com/feeds/videos.xml*' => Http::response(<<<'XML'
                <feed>
                    <entry>
                        <id>yt:video:5ltAy1W6k-Q</id>
                        <title>New Video Title</title>
                        <summary>Video description</summary>
                        <published>2025-01-01T00:00:00+00:00</published>
                    </entry>
                </feed>
                XML, 200),

                'https://discord.com/api/webhooks/test' => Http::response(null, 204),
            ]);

            $action = new CheckForVideos;
            $action->execute($channel);

            Mail::assertSent(NewVideoMail::class);
            Http::assertSent(function ($request) {
                return $request->url() === 'https://discord.com/api/webhooks/test';
            });
        });

        it('does not send discord notifications on first-time import', function (): void {
            Config::set('app.discord_webhook_url', 'https://discord.com/api/webhooks/test');

            $channel = Channel::factory()->create([
                'channel_id' => 'UC_x5XG1OV2P6uZZ5FSM9Ttw',
                'last_checked_at' => null,
            ]);

            Http::fake([
                'https://www.youtube.com/feeds/videos.xml*' => Http::response(<<<'XML'
                <feed>
                    <entry>
                        <id>yt:video:5ltAy1W6k-Q</id>
                        <title>New Video Title</title>
                        <summary>Video description</summary>
                        <published>2025-01-01T00:00:00+00:00</published>
                    </entry>
                </feed>
                XML, 200),
            ]);

            $action = new CheckForVideos;
            $action->execute($channel);

            Mail::assertNothingSent();
            Http::assertNotSent(function ($request) {
                return str_contains((string) $request->url(), 'discord.com/api/webhooks');
            });
        });
    });
});

describe('UpdateLastChecked', function (): void {
    it('updates the last_checked_at timestamp after execution', function (): void {
        Mail::fake();

        $initialDateTime = now();
        $updatedDateTime = now()->addMinute();

        Carbon::setTestNow($initialDateTime);

        $channel = Channel::factory()->create([
            'channel_id' => 'UC_x5XG1OV2P6uZZ5FSM9Ttw',
            'last_checked_at' => now()->subDay(),
        ]);

        $initialLastChecked = $channel->last_checked_at->copy();

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

        Carbon::setTestNow($updatedDateTime);

        $action = new CheckForVideos;
        $action->execute($channel);

        $channel->refresh();

        expect($channel->last_checked_at->gt($initialLastChecked))->toBeTrue();

        Carbon::setTestNow();
    });
});

describe('MultipleVideos', function (): void {
    beforeEach(function (): void {
        Mail::fake();
        Config::set('app.alert_emails', 'email@example.com');
    });

    it('processes and notifies about multiple new videos in a single run', function (): void {
        $channel = Channel::factory()->create([
            'channel_id' => 'UC_x5XG1OV2P6uZZ5FSM9Ttw',
            'last_checked_at' => now()->subDay(),
        ]);

        $rssResponse = <<<'XML'
        <feed>
            <entry>
                <id>yt:video:5ltAy1W6k-Q</id>
                <title>First New Video</title>
                <summary>Video description 1</summary>
                <published>2025-01-01T00:00:00+00:00</published>
            </entry>
            <entry>
                <id>yt:video:6mBgT3W7Ttw</id>
                <title>Second New Video</title>
                <summary>Video description 2</summary>
                <published>2025-01-02T00:00:00+00:00</published>
            </entry>
            <entry>
                <id>yt:video:7nCdR3X8Uuw</id>
                <title>Third New Video</title>
                <summary>Video description 3</summary>
                <published>2025-01-03T00:00:00+00:00</published>
            </entry>
        </feed>
        XML;

        Http::fake([
            'https://www.youtube.com/feeds/videos.xml*' => Http::response($rssResponse, 200),
        ]);

        $action = new CheckForVideos;
        $action->execute($channel);

        expect(Video::count())->toBe(3);

        Mail::assertSent(NewVideoMail::class, 3);

        expect(Video::where('video_id', '5ltAy1W6k-Q')->first()->title)->toBe('First New Video')
            ->and(Video::where('video_id', '6mBgT3W7Ttw')->first()->title)->toBe('Second New Video')
            ->and(Video::where('video_id', '7nCdR3X8Uuw')->first()->title)->toBe('Third New Video');
    });

    it('skips existing videos when processing multiple entries', function (): void {
        $channel = Channel::factory()->create([
            'channel_id' => 'UC_x5XG1OV2P6uZZ5FSM9Ttw',
            'last_checked_at' => now()->subDay(),
        ]);

        Video::create([
            'video_id' => '5ltAy1W6k-Q',
            'title' => 'Existing Video',
            'description' => 'Already in database',
            'published_at' => now(),
            'channel_id' => $channel->id,
        ]);

        $rssResponse = <<<'XML'
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

        Http::fake([
            'https://www.youtube.com/feeds/videos.xml*' => Http::response($rssResponse),
        ]);

        $initialVideoCount = Video::count();

        $action = new CheckForVideos;
        $action->execute($channel);

        expect(Video::count())->toBe($initialVideoCount + 1);

        Mail::assertSent(NewVideoMail::class, 1);

        $existingVideo = Video::where('video_id', '5ltAy1W6k-Q')->first();
        expect($existingVideo->title)->toBe('Existing Video')
            ->and($existingVideo->description)->toBe('Already in database');
    });
});

describe('EmptyFeed', function (): void {
    it('handles RSS feed with valid structure but no entries', function (): void {
        Mail::fake();
        Log::shouldReceive('info')->once();

        $channel = Channel::factory()->create([
            'channel_id' => 'UC_x5XG1OV2P6uZZ5FSM9Ttw',
            'last_checked_at' => now()->subDay(),
        ]);

        $rssResponse = <<<'XML'
        <feed>
        </feed>
        XML;

        Http::fake([
            'https://www.youtube.com/feeds/videos.xml*' => Http::response($rssResponse),
        ]);

        $action = new CheckForVideos;
        $action->execute($channel);

        expect(Video::count())->toBe(0);

        Mail::assertNothingSent();
    });
});

describe('DateHandling', function (): void {
    it('correctly parses and stores the published date from RSS feed', function (): void {
        Mail::fake();
        Config::set('app.alert_emails', 'email@example.com');

        $channel = Channel::factory()->create([
            'channel_id' => 'UC_x5XG1OV2P6uZZ5FSM9Ttw',
            'last_checked_at' => now()->subDay(),
        ]);

        $rssResponse = <<<'XML'
        <feed>
            <entry>
                <id>yt:video:5ltAy1W6k-Q</id>
                <title>New Video Title</title>
                <summary>Video description</summary>
                <published>2025-01-15T13:45:30+00:00</published>
            </entry>
        </feed>
        XML;

        Http::fake([
            'https://www.youtube.com/feeds/videos.xml*' => Http::response($rssResponse, 200),
        ]);

        $action = new CheckForVideos;
        $action->execute($channel);

        $video = Video::where('video_id', '5ltAy1W6k-Q')->first();

        expect($video->published_at->format('Y-m-d H:i:s'))->toBe('2025-01-15 13:45:30')
            ->and($video->published_at->timezone->getName())->toBe('UTC');
    });
});
