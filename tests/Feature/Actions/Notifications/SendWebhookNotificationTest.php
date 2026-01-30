<?php

declare(strict_types=1);

use App\Actions\Notifications\SendWebhookNotification;
use App\Models\Channel;
use App\Models\Video;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

beforeEach(function (): void {
    Video::truncate();
    Channel::truncate();
});

it('sends a post webhook request successfully', function (): void {
    $url = 'https://example.com/webhook';
    Config::set('app.webhook_post_url', $url);

    $channel = Channel::factory()->create([
        'name' => 'Test Channel',
        'channel_id' => 'UC_test123',
    ]);

    $video = Video::factory()->create([
        'channel_id' => $channel->id,
        'video_id' => 'test123',
        'title' => 'Test Video',
        'description' => 'Test Description',
        'published_at' => \Illuminate\Support\Facades\Date::now(),
    ]);

    Http::fake([$url => Http::response(null, 204)]);

    $action = new SendWebhookNotification;
    $result = $action->execute($video);

    expect($result)->toBeTrue();

    Http::assertSent(function ($request) {
        $data = $request->data();

        return
            $request->url() === 'https://example.com/webhook' &&
            isset($data['title']) &&
            $data['title'] === 'Test Video' &&
            isset($data['video_url']) &&
            $data['video_url'] === 'https://www.youtube.com/watch?v=test123' &&
            isset($data['thumbnail']) &&
            $data['thumbnail'] === 'https://i.ytimg.com/vi/test123/hqdefault.jpg' &&
            isset($data['published_at']) &&
            isset($data['published_at_formatted']) &&
            isset($data['channel']) &&
            isset($data['channel']['label']) &&
            $data['channel']['label'] === 'Test Channel' &&
            isset($data['channel']['url']);
    });
});

it('returns false when the post webhook is not configured', function (): void {
    Config::set('app.webhook_post_url', null);

    $channel = Channel::factory()->create([
        'name' => 'Test Channel',
        'channel_id' => 'UC_test123',
    ]);

    $video = Video::factory()->create([
        'channel_id' => $channel->id,
        'video_id' => 'test123',
    ]);

    Http::fake();
    Log::shouldReceive('debug')->never();

    $action = new SendWebhookNotification;
    $result = $action->execute($video);

    expect($result)->toBeFalse();
    Http::assertNothingSent();
});

it('handles exceptions during the webhook', function (): void {
    $url = 'https://invalid-url.example/webhook';
    Config::set('app.webhook_post_url', $url);

    $channel = Channel::factory()->create([
        'name' => 'Test Channel',
        'channel_id' => 'UC_test123',
    ]);

    $video = Video::factory()->create([
        'channel_id' => $channel->id,
        'video_id' => 'test123',
    ]);

    Http::fake([
        $url => Http::response('Error message', 500),
    ]);

    Log::shouldReceive('error')
        ->once()
        ->with('An error occurred while sending the webhook notification.', \Mockery::on(function ($data) use ($video) {
            return isset($data['video_id']) && $data['video_id'] === $video->id && isset($data['response']);
        }));

    $action = new SendWebhookNotification;
    $result = $action->execute($video);

    expect($result)->toBeFalse();
});

it('handles connection exceptions during the webhook', function (): void {
    $url = 'https://unreachable-url.example/webhook';
    Config::set('app.webhook_post_url', $url);

    $channel = Channel::factory()->create([
        'name' => 'Test Channel',
        'channel_id' => 'UC_test123',
    ]);

    $video = Video::factory()->create([
        'channel_id' => $channel->id,
        'video_id' => 'test123',
    ]);

    Http::fake([
        $url => Http::response(function (): void {
            throw new ConnectionException('Connection failed');
        }),
    ]);

    Log::shouldReceive('error')
        ->once()
        ->with('An error occurred while sending the webhook notification.', \Mockery::on(function ($data) use ($video) {
            return isset($data['video_id'], $data['exception']) && $data['video_id'] === $video->id;
        }));

    $action = new SendWebhookNotification;
    $result = $action->execute($video);

    expect($result)->toBeFalse();
});
