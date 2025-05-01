<?php

declare(strict_types=1);

use App\Actions\Notifications\SendDiscordNotification;
use App\Actions\Notifications\SendEmailNotification;
use App\Actions\Notifications\SendVideoNotifications;
use App\Models\Channel;
use App\Models\Video;
use Illuminate\Support\Facades\Config;
use Mockery\MockInterface;

beforeEach(function (): void {
    Channel::truncate();
    Video::truncate();
});

describe('SendVideoNotifications', function (): void {
    it('sends both email and discord notifications when both are configured', function (): void {
        Config::set('app.alert_emails', 'email@example.com');
        Config::set('app.discord_webhook_url', 'https://discord.com/api/webhooks/test');

        $channel = Channel::factory()->create();
        $video = Video::factory()->create(['channel_id' => $channel->id]);

        $mockSendEmailNotification = $this->mock(SendEmailNotification::class, function (MockInterface $mock): void {
            $mock->shouldReceive('execute')->once();
        });

        $mockSendDiscordNotification = $this->mock(SendDiscordNotification::class, function (MockInterface $mock): void {
            $mock->shouldReceive('execute')->once();
        });

        $action = new SendVideoNotifications($mockSendDiscordNotification, $mockSendEmailNotification);
        $action->execute($video);
    });

    it('sends only email notifications when discord is not configured', function (): void {
        Config::set('app.alert_emails', 'email@example.com');
        Config::set('app.discord_webhook_url', null);

        $channel = Channel::factory()->create();
        $video = Video::factory()->create(['channel_id' => $channel->id]);

        $mockSendEmailNotification = $this->mock(SendEmailNotification::class, function (MockInterface $mock): void {
            $mock->shouldReceive('execute')->once();
        });

        $mockSendDiscordNotification = $this->mock(SendDiscordNotification::class, function (MockInterface $mock): void {
            $mock->shouldNotReceive('execute');
        });

        $action = new SendVideoNotifications($mockSendDiscordNotification, $mockSendEmailNotification);
        $action->execute($video);
    });

    it('sends only discord notifications when email is not configured', function (): void {
        Config::set('app.alert_emails', null);
        Config::set('app.discord_webhook_url', 'https://discord.com/api/webhooks/test');

        $channel = Channel::factory()->create();
        $video = Video::factory()->create(['channel_id' => $channel->id]);

        $mockSendEmailNotification = $this->mock(SendEmailNotification::class, function (MockInterface $mock): void {
            $mock->shouldNotReceive('execute');
        });

        $mockSendDiscordNotification = $this->mock(SendDiscordNotification::class, function (MockInterface $mock): void {
            $mock->shouldReceive('execute')->once();
        });

        $action = new SendVideoNotifications($mockSendDiscordNotification, $mockSendEmailNotification);
        $action->execute($video);
    });

    it('sends no notifications when neither email nor discord is configured', function (): void {
        Config::set('app.alert_emails', null);
        Config::set('app.discord_webhook_url', null);

        $channel = Channel::factory()->create();
        $video = Video::factory()->create(['channel_id' => $channel->id]);

        $mockSendEmailNotification = $this->mock(SendEmailNotification::class, function (MockInterface $mock): void {
            $mock->shouldNotReceive('execute');
        });

        $mockSendDiscordNotification = $this->mock(SendDiscordNotification::class, function (MockInterface $mock): void {
            $mock->shouldNotReceive('execute');
        });

        $action = new SendVideoNotifications($mockSendDiscordNotification, $mockSendEmailNotification);
        $action->execute($video);
    });

    it('sends notifications to multiple email addresses when configured', function (): void {
        Config::set('app.alert_emails', ['email1@example.com', 'email2@example.com']);
        Config::set('app.discord_webhook_url', null);

        $channel = Channel::factory()->create();
        $video = Video::factory()->create(['channel_id' => $channel->id]);

        $mockSendEmailNotification = $this->mock(SendEmailNotification::class, function (MockInterface $mock): void {
            $mock->shouldReceive('execute')->once();
        });

        $mockSendDiscordNotification = $this->mock(SendDiscordNotification::class, function (MockInterface $mock): void {
            $mock->shouldNotReceive('execute');
        });

        $action = new SendVideoNotifications($mockSendDiscordNotification, $mockSendEmailNotification);
        $action->execute($video);
    });
});
