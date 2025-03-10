<?php

use App\Actions\CheckForVideosAction;
use App\Mail\NewVideoMail;
use App\Models\Channel;
use App\Models\Video;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

// Ensure the database is clean before running each test
beforeEach(function () {
    Channel::truncate();
    Video::truncate();
});

it('sends a mailable if a new video is found', function () {
    Config::set('app.alert_emails', 'email@example.com');
    // Mock Mail facade
    Mail::fake();

    // Create a test channel with last_checked_at set
    $channel = Channel::factory()->create([
        'channel_id' => 'UC_x5XG1OV2P6uZZ5FSM9Ttw', // Example channel ID
        'last_checked_at' => now()->subDay(), // Simulate that the channel has been checked before
    ]);

    // Mock HTTP response for the RSS feed with a new video
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

    // Execute the action
    $action = new CheckForVideosAction;
    $action->execute($channel);

    // Assert that the email was sent
    Mail::assertSent(NewVideoMail::class, function ($mail) {
        return $mail->hasTo('email@example.com');
    });

    // Assert that the new video was added to the database
    expect(Video::where('video_id', '5ltAy1W6k-Q')->exists())->toBeTrue();
});

it('does not send a mailable on first-time import', function () {
    // Mock Mail facade
    Mail::fake();

    // Create a test channel without last_checked_at
    $channel = Channel::factory()->create([
        'channel_id' => 'UC_x5XG1OV2P6uZZ5FSM9Ttw', // Example channel ID
        'last_checked_at' => null, // Simulate first-time check
    ]);

    // Mock HTTP response for the RSS feed with new videos
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

    // Execute the action
    $action = new CheckForVideosAction;
    $action->execute($channel);

    // Assert that no email was sent
    Mail::assertNothingSent();

    // Assert that the video was added to the database
    expect(Video::where('video_id', '5ltAy1W6k-Q')->exists())->toBeTrue();
});

it('does not send a mailable if no new videos are found', function () {
    // Mock Mail facade
    Mail::fake();

    // Create a test channel with last_checked_at set
    $channel = Channel::factory()->create([
        'channel_id' => 'UC_x5XG1OV2P6uZZ5FSM9Ttw', // Example channel ID
        'last_checked_at' => now()->subDay(), // Simulate that the channel has been checked before
    ]);

    // Mock HTTP response for the RSS feed with no new videos
    $rssResponse = <<<'XML'
    <feed>
    </feed>
    XML;

    Http::fake([
        'https://www.youtube.com/feeds/videos.xml*' => Http::response($rssResponse, 200),
    ]);

    // Execute the action
    $action = new CheckForVideosAction;
    $action->execute($channel);

    // Assert that no email was sent
    Mail::assertNothingSent();
});

it('logs an error if the RSS feed fetch fails', function () {
    Mail::fake();
    // Mock the Log facade
    Log::shouldReceive('error')->once();

    // Create a test channel
    $channel = Channel::factory()->create([
        'channel_id' => 'UC_x5XG1OV2P6uZZ5FSM9Ttw', // Example channel ID
    ]);

    // Mock HTTP response for the RSS feed to fail
    Http::fake([
        'https://www.youtube.com/feeds/videos.xml*' => Http::response(null, 500),
    ]);

    // Execute the action
    $action = new CheckForVideosAction;
    $action->execute($channel);

    // Assert that no email was sent
    Mail::assertNothingSent();
});

it('logs an info message if the RSS feed is malformed and no emails are sent', function () {
    Log::shouldReceive('info')->once();

    Log::shouldReceive('debug')
        ->twice()
        ->andReturn(null);

    // Mock Mail facade
    Mail::fake();

    // Create a test channel
    $channel = Channel::factory()->create([
        'channel_id' => 'UC_x5XG1OV2P6uZZ5FSM9Ttw', // Example channel ID
    ]);

    // Mock HTTP response for the malformed RSS feed
    $rssResponse = <<<'XML'
    <feed>
        <!-- Malformed entry -->
        <entry>
            <id>yt:video:5ltAy1W6k-Q</id>
            <title>New Video Title</title>
            <summary>Video description</summary>
        </entry>
    </feed>
    XML;

    Http::fake([
        'https://www.youtube.com/feeds/videos.xml*' => Http::response($rssResponse, 200),
    ]);

    // Execute the action
    $action = new CheckForVideosAction;
    $action->execute($channel);

    // Assert that no email was sent
    Mail::assertNothingSent();
});

it('ignores videos with the exact word LIVE in the title', function () {
    Mail::fake();

    $channel = Channel::factory()->create([
        'channel_id' => 'UC_x5XG1OV2P6uZZ5FSM9Ttw', // Example channel ID
        'last_checked_at' => now()->subDay(), // Simulate that the channel has been checked before
    ]);

    $rssResponse = <<<'XML'
    <feed>
        <entry>
            <id>yt:video:5ltAy1W6k-Q</id>
            <title>LIVE Video Title</title>
            <summary>Video description</summary>
            <published>2025-01-01T00:00:00+00:00</published>
        </entry>
    </feed>
    XML;

    Http::fake([
        'https://www.youtube.com/feeds/videos.xml*' => Http::response($rssResponse, 200),
    ]);

    $action = new CheckForVideosAction;
    $action->execute($channel);

    Mail::assertNothingSent();

    expect(Video::where('video_id', '5ltAy1W6k-Q')->exists())->toBeFalse();
});

// Essential Discord integration tests that should remain in this file

it('sends both email and discord notifications for new videos', function () {
    Config::set('app.alert_email', 'email@example.com');
    Config::set('app.discord_webhook_url', 'https://discord.com/api/webhooks/test');

    Mail::fake();

    // Create a test channel with last_checked_at set
    $channel = Channel::factory()->create([
        'channel_id' => 'UC_x5XG1OV2P6uZZ5FSM9Ttw',
        'last_checked_at' => now()->subDay(),
    ]);

    // Mock HTTP responses
    Http::fake([
        // Mock the YouTube RSS feed
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

        // Mock the Discord webhook response
        'https://discord.com/api/webhooks/test' => Http::response(null, 204),
    ]);

    // Execute the action
    $action = new CheckForVideosAction;
    $action->execute($channel);

    // Assert that both notification types were sent
    Mail::assertSent(NewVideoMail::class);
    Http::assertSent(function ($request) {
        return $request->url() === 'https://discord.com/api/webhooks/test';
    });
});

it('does not send discord notifications on first-time import', function () {
    Config::set('app.discord_webhook_url', 'https://discord.com/api/webhooks/test');

    Mail::fake();

    // Create a test channel without last_checked_at
    $channel = Channel::factory()->create([
        'channel_id' => 'UC_x5XG1OV2P6uZZ5FSM9Ttw',
        'last_checked_at' => null, // Simulate first-time check
    ]);

    // Mock HTTP response for the RSS feed
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

    // Execute the action
    $action = new CheckForVideosAction;
    $action->execute($channel);

    // Assert that no notifications were sent
    Mail::assertNothingSent();
    Http::assertNotSent(function ($request) {
        return strpos($request->url(), 'discord.com/api/webhooks') !== false;
    });
});

it('sends notification to multiple email addresses when configured', function () {
    // Configure multiple email addresses
    Config::set('app.alert_emails', ['email1@example.com', 'email2@example.com']);

    // Mock Mail facade
    Mail::fake();

    // Create a test channel with last_checked_at set
    $channel = Channel::factory()->create([
        'channel_id' => 'UC_x5XG1OV2P6uZZ5FSM9Ttw',
        'last_checked_at' => now()->subDay(), // Simulate that the channel has been checked before
    ]);

    // Mock HTTP response for the RSS feed with a new video
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

    // Execute the action
    $action = new CheckForVideosAction;
    $action->execute($channel);

    // Assert that the email was sent to both addresses
    Mail::assertSent(NewVideoMail::class, function ($mail) {
        return $mail->hasTo('email1@example.com') &&
            $mail->hasTo('email2@example.com');
    });

    // Assert that the new video was added to the database
    expect(Video::where('video_id', '5ltAy1W6k-Q')->exists())->toBeTrue();
});

it('does not send any notifications if the channel has been muted', function () {
    Config::set('app.alert_emails', 'email@example.com');
    Config::set('app.discord_webhook_url', 'https://discord.com/api/webhooks/test');
    Mail::fake();

    $channel = Channel::factory()->muted()->create([
        'channel_id' => 'UC_x5XG1OV2P6uZZ5FSM9Ttw',
        'last_checked_at' => now()->subDay(), // Simulate that the channel has been checked before
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

    $action = new CheckForVideosAction;
    $action->execute($channel);

    Mail::assertNotSent(NewVideoMail::class, function ($mail) {
        return $mail->hasTo('email@example.com');
    });

    Http::assertNotSent(function ($request) {
        return str_contains($request->url(), 'discord.com/api/webhooks');
    });

    expect(Video::where('video_id', '5ltAy1W6k-Q')->exists())->toBeTrue()
        ->and($channel->isMuted())->toBeTrue();
});
