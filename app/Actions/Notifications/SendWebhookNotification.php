<?php

declare(strict_types=1);

namespace App\Actions\Notifications;

use App\Models\Channel;
use App\Models\Video;
use Exception;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SendWebhookNotification
{
    public function execute(Video $video): bool
    {
        $webhookUrl = config('app.webhook_post_url');

        if (empty($webhookUrl)) {
            return false;
        }

        /** @var Channel $channel */
        $channel = $video->channel;

        try {
            $externalRequest = Http::post($webhookUrl, [
                'title' => $video->getAttribute('title'),
                'video_url' => $video->getYoutubeUrl(),
                'thumbnail' => $video->getThumbnailUrl(),
                'published_at' => $video->getAttribute('published_at')->toDateTimeString(),
                'published_at_formatted' => $video->getFormattedPublishedDate(),
                'channel' => [
                    'label' => $channel->getAttribute('name'),
                    'url' => $channel->getChannelUrl(),
                ],
            ]);

            if ($externalRequest->failed()) {
                Log::error('An error occurred while sending the webhook notification.', [
                    'video_id' => $video->id,
                    'response' => $externalRequest->body(),
                ]);

                return false;
            }

            Log::debug('Webhook notification sent.', [
                'video_id' => $video->id,
                'response' => $externalRequest->body(),
            ]);

            return $externalRequest->successful();

        } catch (RequestException $e) {
            Log::error('An error occurred while sending the webhook notification.', [
                'video_id' => $video->id,
                'response' => $e->response->body(),
                'exception' => $e->getMessage(),
            ]);

            return false;
        } catch (Exception $e) {
            Log::error('An error occurred while sending the webhook notification.', [
                'video_id' => $video->id,
                'exception' => $e->getMessage(),
            ]);

            return false;
        }
    }
}
