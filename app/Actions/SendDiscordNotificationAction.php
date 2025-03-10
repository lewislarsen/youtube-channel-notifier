<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Video;
use Exception;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SendDiscordNotificationAction
{
    /**
     * Execute the action to send a Discord notification for a new video.
     *
     * @param  Video  $video  The video to send notification about
     * @return bool Whether the notification was sent successfully
     */
    public function execute(Video $video): bool
    {
        $webhookUrl = Config::get('app.discord_webhook_url');

        if (empty($webhookUrl)) {
            return false;
        }

        $embed = [
            'title' => $video->getAttribute('title'),
            'description' => $video->getAttribute('description'),
            'url' => $video->getYoutubeUrl(),
            'color' => 0xFF0000, // YouTube red
            'timestamp' => $video->getIsoPublishedDate(),
            'thumbnail' => [
                'url' => $video->getThumbnailUrl('maxresdefault'),
            ],
            'footer' => [
                'text' => Config::get('app.name'),
            ],
        ];

        $embed['author'] = [
            'name' => $video->channel->getAttribute('name'),
            'url' => $video->channel->getChannelUrl(),
        ];

        $payload = [
            'content' => '🎬 **New Video Alert!** Check out this new upload from '.$video->channel->getAttribute('name'),
            'embeds' => [$embed],
        ];

        try {
            $response = Http::post($webhookUrl, $payload);

            if ($response->successful()) {
                Log::info("Discord notification sent for video: {$video->getAttribute('title')}");

                return true;
            }

            Log::error("Discord notification failed with status: {$response->status()}", [
                'video_id' => $video->getAttribute('video_id'),
                'response' => $response->body(),
            ]);

            return false;
        } catch (Exception $e) {
            Log::error("Discord notification exception: {$e->getMessage()}", [
                'video_id' => $video->getAttribute('video_id'),
            ]);

            return false;
        }
    }
}
