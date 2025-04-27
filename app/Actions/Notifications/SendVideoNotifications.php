<?php

declare(strict_types=1);

namespace App\Actions\Notifications;

use App\Mail\NewVideoMail;
use App\Models\Channel;
use App\Models\Video;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;

/**
 * Class SendVideoNotifications
 *
 * This action sends notifications for new videos.
 */
class SendVideoNotifications
{
    public function __construct(private readonly SendDiscordNotification $sendDiscordNotification)
    {
    }

    /**
     * Send all configured notifications for a new video.
     *
     * @param  Video  $video  The new video.
     * @param  Channel  $channel  The channel to which the video belongs.
     */
    public function execute(Video $video, Channel $channel): void
    {
        $this->sendEmailNotification($video, $channel);
        $this->sendDiscordNotification($video);
    }

    /**
     * Send an email notification for a new video.
     *
     * @param  Video  $video  The new video.
     * @param  Channel  $channel  The channel to which the video belongs.
     */
    private function sendEmailNotification(Video $video, Channel $channel): void
    {
        Mail::to(Config::get('app.alert_emails'))->send(new NewVideoMail($video, $channel));
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
