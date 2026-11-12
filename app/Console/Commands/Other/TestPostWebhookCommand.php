<?php

declare(strict_types=1);

namespace App\Console\Commands\Other;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

#[Description('Sends a test POST request to the location specified in the env file.')]
#[Signature('other:post-test')]
class TestPostWebhookCommand extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $configValue = config('app.webhook_post_url');

        if (empty($configValue)) {
            $this->components->error('Unable to send a test POST request. No webhook URL is set in the config.');

            return;
        }

        $response = Http::post(config('app.webhook_post_url'), [
            'title' => 'Test Title',
            'video_url' => 'https://www.youtube.com/watch?v=1234567890',
            'thumbnail' => 'https://i.ytimg.com/vi/1234567890/hqdefault.jpg',
            'published_at' => Date::now()->toDateTimeString(),
            'published_at_formatted' => Date::now()->format('d M Y h:i A'),
            'channel' => [
                'label' => 'Test Channel',
                'url' => 'https://www.youtube.com/channel/UC1234567890',
            ],
        ]);

        if ($response->failed()) {
            Log::error('An error occurred while sending the webhook notification.', [
                'response' => $response->body(),
            ]);

            $this->components->error('An error occurred while sending the webhook notification. View the logs for more details.');

            return;
        }

        $this->components->success('Webhook notification sent successfully.');
    }
}
