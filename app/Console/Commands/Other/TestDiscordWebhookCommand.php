<?php

declare(strict_types=1);

namespace App\Console\Commands\Other;

use App\Enums\Colour;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;

class TestDiscordWebhookCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'other:discord-webhook-test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sends a test Discord webhook notification to the configured URL';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $webhookUrl = Config::get('app.discord_webhook_url');

        if (empty($webhookUrl)) {
            $this->components->error('Unable to send a test Discord notification. No Discord webhook URL is set in the config.');

            return;
        }

        $embed = [
            'title' => 'Test Notification',
            'description' => 'This is a test notification from the application.',
            'url' => config('app.url', 'https://example.com'),
            'color' => Colour::YouTube_Red->value,
            'timestamp' => \Illuminate\Support\Facades\Date::now()->toIso8601String(),
            'thumbnail' => [
                'url' => URL::asset('assets/white-full.png'),
            ],
            'footer' => [
                'text' => Config::get('app.name'),
            ],
        ];

        $payload = [
            'content' => '**Test Notification** This is a test message from '.Config::get('app.name'),
            'embeds' => [$embed],
            'avatar_url' => URL::asset('assets/white-full.png'),
            'username' => Config::get('app.name'),
        ];

        $response = Http::post($webhookUrl, $payload);

        if ($response->failed()) {
            Log::error('An error occurred while sending the Discord webhook notification.', [
                'response' => $response->body(),
            ]);

            $this->components->error('An error occurred while sending the Discord webhook notification. View the logs for more details.');

            return;
        }

        $this->components->success('Discord webhook notification sent successfully.');
    }
}
