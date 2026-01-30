<?php

declare(strict_types=1);

use App\Actions\Notifications\SendVideoNotifications;
use App\Actions\YouTube\ProcessVideos;
use App\Models\Channel;
use App\Models\Video;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Mockery\MockInterface;

beforeEach(function (): void {
    Channel::truncate();
    Video::truncate();
});

describe('ProcessVideos', function (): void {
    it('handles first-time import of videos without sending notifications', function (): void {
        $channel = Channel::factory()->create([
            'last_checked_at' => null,
            'channel_id' => 'UC_x5XG1OV2P6uZZ5FSM9Ttw',
        ]);

        $mockSendVideoNotifications = Mockery::mock(SendVideoNotifications::class);
        // Expect notifications NOT to be sent
        $mockSendVideoNotifications->shouldNotReceive('execute');

        $newVideos = [
            [
                'video_id' => '5ltAy1W6k-Q',
                'title' => 'First New Video',
                'description' => 'Video description 1',
                'published_at' => \Illuminate\Support\Facades\Date::parse('2025-01-01T00:00:00+00:00'),
                'channel_id' => $channel->id,
            ],
            [
                'video_id' => '6mBgT3W7Ttw',
                'title' => 'Second New Video',
                'description' => 'Video description 2',
                'published_at' => \Illuminate\Support\Facades\Date::parse('2025-01-02T00:00:00+00:00'),
                'channel_id' => $channel->id,
            ],
        ];

        Log::shouldReceive('info')->once();

        $action = new ProcessVideos($mockSendVideoNotifications);
        $action->execute($newVideos, $channel);

        expect(Video::count())->toBe(2)
            ->and(Video::where('video_id', '5ltAy1W6k-Q')->exists())->toBeTrue()
            ->and(Video::where('video_id', '6mBgT3W7Ttw')->exists())->toBeTrue();
    });

    it('sends notifications for new videos after first-time import', function (): void {
        $channel = Channel::factory()->create([
            'last_checked_at' => now()->subDay(),
            'channel_id' => 'UC_x5XG1OV2P6uZZ5FSM9Ttw',
        ]);

        $mockSendVideoNotifications = $this->mock(SendVideoNotifications::class, function (MockInterface $mock): void {
            $mock->shouldReceive('execute')->twice();
        });

        $newVideos = [
            [
                'video_id' => '5ltAy1W6k-Q',
                'title' => 'First New Video',
                'description' => 'Video description 1',
                'published_at' => \Illuminate\Support\Facades\Date::parse('2025-01-01T00:00:00+00:00'),
                'channel_id' => $channel->id,
            ],
            [
                'video_id' => '6mBgT3W7Ttw',
                'title' => 'Second New Video',
                'description' => 'Video description 2',
                'published_at' => \Illuminate\Support\Facades\Date::parse('2025-01-02T00:00:00+00:00'),
                'channel_id' => $channel->id,
            ],
        ];

        Log::shouldReceive('info')->twice();

        $action = new ProcessVideos($mockSendVideoNotifications);
        $action->execute($newVideos, $channel);

        expect(Video::count())->toBe(2)
            ->and(Video::where('video_id', '5ltAy1W6k-Q')->exists())->toBeTrue()
            ->and(Video::where('video_id', '6mBgT3W7Ttw')->exists())->toBeTrue();
    });

    it('does not send notifications for new videos if channel is muted', function (): void {
        $channel = Channel::factory()->muted()->create([
            'last_checked_at' => now()->subDay(),
            'channel_id' => 'UC_x5XG1OV2P6uZZ5FSM9Ttw',
        ]);

        $mockSendVideoNotifications = Mockery::mock(SendVideoNotifications::class);
        // Expect notifications NOT to be sent
        $mockSendVideoNotifications->shouldNotReceive('execute');

        $newVideos = [
            [
                'video_id' => '5ltAy1W6k-Q',
                'title' => 'New Video Title',
                'description' => 'Video description',
                'published_at' => \Illuminate\Support\Facades\Date::parse('2025-01-01T00:00:00+00:00'),
                'channel_id' => $channel->id,
            ],
        ];

        Log::shouldReceive('info')->once();

        $action = new ProcessVideos($mockSendVideoNotifications);
        $action->execute($newVideos, $channel);

        expect(Video::count())->toBe(1)
            ->and(Video::where('video_id', '5ltAy1W6k-Q')->exists())->toBeTrue()
            ->and($channel->isMuted())->toBeTrue();
    });

    it('handles empty array of new videos gracefully', function (): void {
        $channel = Channel::factory()->create([
            'last_checked_at' => now()->subDay(),
        ]);

        $mockSendVideoNotifications = Mockery::mock(SendVideoNotifications::class);
        // No notifications should be sent for empty array
        $mockSendVideoNotifications->shouldNotReceive('execute');

        $newVideos = [];

        $action = new ProcessVideos($mockSendVideoNotifications);
        $action->execute($newVideos, $channel);

        expect(Video::count())->toBe(0);
    });
});
