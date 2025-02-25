<?php

use App\Http\Actions\SendDiscordNotificationAction;
use App\Models\Channel;
use App\Models\Video;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    Video::truncate();
    Channel::truncate();
});

it('sends a discord notification successfully', function () {
    // Configure webhook URL
    Config::set('app.discord_webhook_url', 'https://discord.com/api/webhooks/test');

    // Create a channel and video using factories
    $channel = Channel::factory()->create([
        'name' => 'Test Channel',
        'channel_id' => 'UC_test123',
    ]);

    $video = Video::factory()->create([
        'channel_id' => $channel->id,
        'video_id' => 'test123',
        'title' => 'Test Video',
        'description' => 'Test Description',
        'published_at' => Carbon::now(),
    ]);

    // Mock HTTP response for Discord webhook
    Http::fake([
        'https://discord.com/api/webhooks/test' => Http::response(null, 204),
    ]);

    // Execute the action
    $action = new SendDiscordNotificationAction;
    $result = $action->execute($video);

    // Assert the result is true
    expect($result)->toBeTrue();

    // Assert that HTTP request to Discord was made with correct data
    Http::assertSent(function ($request) {
        $data = $request->data();

        return
            $request->url() === 'https://discord.com/api/webhooks/test' &&
            isset($data['embeds'][0]['title']) &&
            $data['embeds'][0]['title'] === 'Test Video';
    });
});

it('returns false when discord webhook is not configured', function () {
    // Ensure webhook URL is not configured
    Config::set('app.discord_webhook_url', null);

    // Create a channel and video
    $channel = Channel::factory()->create();
    $video = Video::factory()->create(['channel_id' => $channel->id]);

    // Execute the action
    $action = new SendDiscordNotificationAction;
    $result = $action->execute($video);

    // Assert the result is false
    expect($result)->toBeFalse();

    // Assert no HTTP requests were made
    Http::assertNothingSent();
});

it('handles error responses from discord', function () {
    // Configure webhook URL
    Config::set('app.discord_webhook_url', 'https://discord.com/api/webhooks/test');

    // Create a channel and video
    $channel = Channel::factory()->create();
    $video = Video::factory()->create([
        'channel_id' => $channel->id,
        'video_id' => 'test123',
        'title' => 'Test Video',
    ]);

    // Mock HTTP response for Discord webhook to fail
    Http::fake([
        'https://discord.com/api/webhooks/test' => Http::response('Rate limited', 429),
    ]);

    // Execute the action
    $action = new SendDiscordNotificationAction;
    $result = $action->execute($video);

    // Assert the result is false
    expect($result)->toBeFalse();

    // Assert that HTTP request to Discord was attempted
    Http::assertSent(function ($request) {
        return $request->url() === 'https://discord.com/api/webhooks/test';
    });
});

it('handles exceptions during discord notification', function () {
    // Configure webhook URL
    Config::set('app.discord_webhook_url', 'https://discord.com/api/webhooks/test');

    // Create a channel and video
    $channel = Channel::factory()->create();
    $video = Video::factory()->create(['channel_id' => $channel->id]);

    // Use a mock to simulate the Http facade throwing an exception
    Http::shouldReceive('post')
        ->once()
        ->andThrow(new Exception('Connection failed'));

    // Execute the action
    $action = new SendDiscordNotificationAction;
    $result = $action->execute($video);

    // Assert the result is false
    expect($result)->toBeFalse();
});
