<?php

namespace App\Http\Actions;

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
            'title' => $video->title,
            'description' => $video->description,
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
            'name' => $video->channel->name,
            'url' => $video->channel->getChannelUrl(),
        ];

        $payload = [
            'embeds' => [$embed],
        ];

        try {
            $response = Http::post($webhookUrl, $payload);

            if ($response->successful()) {
                Log::info("Discord notification sent for video: {$video->title}");

                return true;
            }

            Log::error("Discord notification failed with status: {$response->status()}", [
                'video_id' => $video->video_id,
                'response' => $response->body(),
            ]);

            return false;
        } catch (Exception $e) {
            Log::error("Discord notification exception: {$e->getMessage()}", [
                'video_id' => $video->video_id,
            ]);

            return false;
        }
    }
}
