<?php

declare(strict_types=1);

namespace App\Actions\Notifications;

use App\Models\Video;
use Illuminate\Support\Facades\Config;

/**
 * Class SendVideoNotifications
 *
 * This action sends notifications for new videos.
 */
class SendVideoNotifications
{
    public function __construct(private readonly SendDiscordNotification $sendDiscordNotification,
        private readonly SendEmailNotification $sendEmailNotification) {}

    /**
     * Send all configured notifications for a new video.
     *
     * @param  Video  $video  The new video.
     */
    public function execute(Video $video): void
    {
        $this->sendEmailNotification($video);
        $this->sendDiscordNotification($video);
    }

    /**
     * Send an email notification to all emails for a new video.
     *
     * @param  Video  $video  The new video.
     */
    private function sendEmailNotification(Video $video): void
    {
        if (Config::get('app.alert_emails')) {
            $this->sendEmailNotification->execute($video);
        }
    }

    /**
     * Send a Discord notification for a new video if Discord is configured.
     *
     * @param  Video  $video  The new video.
     */
    private function sendDiscordNotification(Video $video): void
    {
        if (Config::get('app.discord_webhook_url')) {
            $this->sendDiscordNotification->execute($video);
        }
    }
}
