<?php

declare(strict_types=1);

use App\Actions\Notifications\SendDiscordNotification;
use App\Actions\Notifications\SendEmailNotification;
use App\Actions\Notifications\SendVideoNotifications;
use App\Actions\Notifications\SendWebhookNotification;
use App\Models\Channel;
use App\Models\Video;
use Illuminate\Support\Facades\Config;
use Mockery\MockInterface;

beforeEach(function (): void {
    Channel::truncate();
    Video::truncate();
});

describe('SendVideoNotifications', function (): void {
    it('sends email, discord, and webhook notifications when all are configured', function (): void {
        Config::set('app.alert_emails', 'email@example.com');
        Config::set('app.discord_webhook_url', 'https://discord.com/api/webhooks/test');
        Config::set('app.webhook_post_url', 'https://example.com/webhook');

        $channel = Channel::factory()->create();
        $video = Video::factory()->create(['channel_id' => $channel->id]);

        $mockSendEmailNotification = $this->mock(SendEmailNotification::class, function (MockInterface $mock): void {
            $mock->shouldReceive('execute')->once();
        });

        $mockSendDiscordNotification = $this->mock(SendDiscordNotification::class, function (MockInterface $mock): void {
            $mock->shouldReceive('execute')->once();
        });

        $mockSendWebhookNotification = $this->mock(SendWebhookNotification::class, function (MockInterface $mock): void {
            $mock->shouldReceive('execute')->once();
        });

        $action = new SendVideoNotifications(
            $mockSendDiscordNotification,
            $mockSendEmailNotification,
            $mockSendWebhookNotification
        );
        $action->execute($video);
    });

    it('sends only email and discord notifications when webhook is not configured', function (): void {
        Config::set('app.alert_emails', 'email@example.com');
        Config::set('app.discord_webhook_url', 'https://discord.com/api/webhooks/test');
        Config::set('app.webhook_post_url', null);

        $channel = Channel::factory()->create();
        $video = Video::factory()->create(['channel_id' => $channel->id]);

        $mockSendEmailNotification = $this->mock(SendEmailNotification::class, function (MockInterface $mock): void {
            $mock->shouldReceive('execute')->once();
        });

        $mockSendDiscordNotification = $this->mock(SendDiscordNotification::class, function (MockInterface $mock): void {
            $mock->shouldReceive('execute')->once();
        });

        $mockSendWebhookNotification = $this->mock(SendWebhookNotification::class, function (MockInterface $mock): void {
            $mock->shouldNotReceive('execute');
        });

        $action = new SendVideoNotifications(
            $mockSendDiscordNotification,
            $mockSendEmailNotification,
            $mockSendWebhookNotification
        );
        $action->execute($video);
    });

    it('sends only email notifications when discord and webhook are not configured', function (): void {
        Config::set('app.alert_emails', 'email@example.com');
        Config::set('app.discord_webhook_url', null);
        Config::set('app.webhook_post_url', null);

        $channel = Channel::factory()->create();
        $video = Video::factory()->create(['channel_id' => $channel->id]);

        $mockSendEmailNotification = $this->mock(SendEmailNotification::class, function (MockInterface $mock): void {
            $mock->shouldReceive('execute')->once();
        });

        $mockSendDiscordNotification = $this->mock(SendDiscordNotification::class, function (MockInterface $mock): void {
            $mock->shouldNotReceive('execute');
        });

        $mockSendWebhookNotification = $this->mock(SendWebhookNotification::class, function (MockInterface $mock): void {
            $mock->shouldNotReceive('execute');
        });

        $action = new SendVideoNotifications(
            $mockSendDiscordNotification,
            $mockSendEmailNotification,
            $mockSendWebhookNotification
        );
        $action->execute($video);
    });

    it('sends only discord notifications when email and webhook are not configured', function (): void {
        Config::set('app.alert_emails', null);
        Config::set('app.discord_webhook_url', 'https://discord.com/api/webhooks/test');
        Config::set('app.webhook_post_url', null);

        $channel = Channel::factory()->create();
        $video = Video::factory()->create(['channel_id' => $channel->id]);

        $mockSendEmailNotification = $this->mock(SendEmailNotification::class, function (MockInterface $mock): void {
            $mock->shouldNotReceive('execute');
        });

        $mockSendDiscordNotification = $this->mock(SendDiscordNotification::class, function (MockInterface $mock): void {
            $mock->shouldReceive('execute')->once();
        });

        $mockSendWebhookNotification = $this->mock(SendWebhookNotification::class, function (MockInterface $mock): void {
            $mock->shouldNotReceive('execute');
        });

        $action = new SendVideoNotifications(
            $mockSendDiscordNotification,
            $mockSendEmailNotification,
            $mockSendWebhookNotification
        );
        $action->execute($video);
    });

    it('sends only webhook notifications when email and discord are not configured', function (): void {
        Config::set('app.alert_emails', null);
        Config::set('app.discord_webhook_url', null);
        Config::set('app.webhook_post_url', 'https://example.com/webhook');

        $channel = Channel::factory()->create();
        $video = Video::factory()->create(['channel_id' => $channel->id]);

        $mockSendEmailNotification = $this->mock(SendEmailNotification::class, function (MockInterface $mock): void {
            $mock->shouldNotReceive('execute');
        });

        $mockSendDiscordNotification = $this->mock(SendDiscordNotification::class, function (MockInterface $mock): void {
            $mock->shouldNotReceive('execute');
        });

        $mockSendWebhookNotification = $this->mock(SendWebhookNotification::class, function (MockInterface $mock): void {
            $mock->shouldReceive('execute')->once();
        });

        $action = new SendVideoNotifications(
            $mockSendDiscordNotification,
            $mockSendEmailNotification,
            $mockSendWebhookNotification
        );
        $action->execute($video);
    });

    it('sends no notifications when no notification methods are configured', function (): void {
        Config::set('app.alert_emails', null);
        Config::set('app.discord_webhook_url', null);
        Config::set('app.webhook_post_url', null);

        $channel = Channel::factory()->create();
        $video = Video::factory()->create(['channel_id' => $channel->id]);

        $mockSendEmailNotification = $this->mock(SendEmailNotification::class, function (MockInterface $mock): void {
            $mock->shouldNotReceive('execute');
        });

        $mockSendDiscordNotification = $this->mock(SendDiscordNotification::class, function (MockInterface $mock): void {
            $mock->shouldNotReceive('execute');
        });

        $mockSendWebhookNotification = $this->mock(SendWebhookNotification::class, function (MockInterface $mock): void {
            $mock->shouldNotReceive('execute');
        });

        $action = new SendVideoNotifications(
            $mockSendDiscordNotification,
            $mockSendEmailNotification,
            $mockSendWebhookNotification
        );
        $action->execute($video);
    });

    it('sends notifications to multiple email addresses when configured', function (): void {
        Config::set('app.alert_emails', ['email1@example.com', 'email2@example.com']);
        Config::set('app.discord_webhook_url', null);
        Config::set('app.webhook_post_url', null);

        $channel = Channel::factory()->create();
        $video = Video::factory()->create(['channel_id' => $channel->id]);

        $mockSendEmailNotification = $this->mock(SendEmailNotification::class, function (MockInterface $mock): void {
            $mock->shouldReceive('execute')->once();
        });

        $mockSendDiscordNotification = $this->mock(SendDiscordNotification::class, function (MockInterface $mock): void {
            $mock->shouldNotReceive('execute');
        });

        $mockSendWebhookNotification = $this->mock(SendWebhookNotification::class, function (MockInterface $mock): void {
            $mock->shouldNotReceive('execute');
        });

        $action = new SendVideoNotifications(
            $mockSendDiscordNotification,
            $mockSendEmailNotification,
            $mockSendWebhookNotification
        );
        $action->execute($video);
    });
});
