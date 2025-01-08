<?php

use App\Http\Actions\CheckForVideosAction;
use App\Mail\NewVideoMail;
use App\Models\Channel;
use App\Models\Video;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

// Ensure the database is clean before running each test
beforeEach(function () {
    Channel::truncate();
    Video::truncate();
});

it('sends a mailable if a new video is found', function () {
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
        return $mail->hasTo('lewis@larsens.dev');
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
    // Mock the Log facade
    Log::shouldReceive('info')->twice();

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
