<?php

declare(strict_types=1);

use App\Console\Commands\Other\TestDiscordWebhookCommand;
use App\Enums\Colour;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\URL;

it('cannot send a test Discord notification if the webhook URL is not set', function (): void {
    Http::fake();
    Config::set('app.discord_webhook_url', null);

    $this->artisan(TestDiscordWebhookCommand::class)
        ->expectsOutputToContain('Unable to send a test Discord notification. No Discord webhook URL is set in the config.');

    Http::assertNothingSent();
});

it('can send a test Discord notification if the webhook URL is set', function (): void {
    Http::fake();
    Config::set('app.discord_webhook_url', 'https://discord.com/api/webhooks/example');
    Config::set('app.name', 'Test App');
    Config::set('app.url', 'https://example.com');

    URL::shouldReceive('asset')
        ->with('assets/white-full.png')
        ->andReturn('https://example.com/assets/white-full.png');

    $now = \Illuminate\Support\Facades\Date::create(2025, 5, 1, 12, 0, 0);
    \Illuminate\Support\Facades\Date::setTestNow($now);

    $this->artisan(TestDiscordWebhookCommand::class)
        ->expectsOutputToContain('Discord webhook notification sent successfully.');

    Http::assertSent(function ($request) use ($now) {
        $expectedPayload = [
            'content' => '**Test Notification** This is a test message from Test App',
            'embeds' => [[
                'title' => 'Test Notification',
                'description' => 'This is a test notification from the application.',
                'url' => 'https://example.com',
                'color' => Colour::YouTube_Red->value,
                'timestamp' => $now->toIso8601String(),
                'thumbnail' => [
                    'url' => 'https://example.com/assets/white-full.png',
                ],
                'footer' => [
                    'text' => 'Test App',
                ],
            ]],
            'avatar_url' => 'https://example.com/assets/white-full.png',
            'username' => 'Test App',
        ];

        return $request->url() === 'https://discord.com/api/webhooks/example' &&
            $request->method() === 'POST' &&
            $request->data() === $expectedPayload;
    });

    \Illuminate\Support\Facades\Date::setTestNow();
});

it('can handle a failed Discord webhook request', function (): void {
    Http::fake([
        'https://discord.com/api/webhooks/example' => Http::sequence()
            ->push('', 500),
    ]);

    Config::set('app.discord_webhook_url', 'https://discord.com/api/webhooks/example');

    $this->artisan(TestDiscordWebhookCommand::class)
        ->expectsOutputToContain('An error occurred while sending the Discord webhook notification. View the logs for more details.');

    Http::assertSent(function ($request) {
        return $request->url() === 'https://discord.com/api/webhooks/example' &&
            $request->method() === 'POST';
    });
});
