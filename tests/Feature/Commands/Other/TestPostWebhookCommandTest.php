<?php

declare(strict_types=1);

use App\Console\Commands\Other\TestPostWebhookCommand;

it('cannot send a test POST request if the webhook URL is not set', function (): void {
    Http::fake();
    Config::set('app.webhook_post_url', null);

    $this->artisan(TestPostWebhookCommand::class)
        ->expectsOutputToContain('Unable to send a test POST request. No webhook URL is set in the config.');

    Http::assertNothingSent();
});

it('can send a test POST request if the webhook URL is set', function (): void {
    Http::fake();
    Config::set('app.webhook_post_url', 'https://example.com/webhook');

    $this->artisan(TestPostWebhookCommand::class)
        ->expectsOutputToContain('Webhook notification sent successfully.');

    Http::assertSent(function ($request) {
        return $request->url() === 'https://example.com/webhook' &&
            $request->method() === 'POST' &&
            $request->data() === [
                'title' => 'Test Title',
                'video_url' => 'https://www.youtube.com/watch?v=1234567890',
                'thumbnail' => 'https://i.ytimg.com/vi/1234567890/hqdefault.jpg',
                'published_at' => now()->toDateTimeString(),
                'published_at_formatted' => now()->format('d M Y h:i A'),
                'channel' => [
                    'label' => 'Test Channel',
                    'url' => 'https://www.youtube.com/channel/UC1234567890',
                ],
            ];
    });
});

it('can handle a failed POST request', function (): void {
    Http::fake([
        'https://example.com/webhook' => Http::sequence()
            ->push('', 500),
    ]);

    Config::set('app.webhook_post_url', 'https://example.com/webhook');

    $this->artisan(TestPostWebhookCommand::class)
        ->expectsOutputToContain('An error occurred while sending the webhook notification. View the logs for more details.');

    Http::assertSent(function ($request) {
        return $request->url() === 'https://example.com/webhook' &&
            $request->method() === 'POST';
    });
});
