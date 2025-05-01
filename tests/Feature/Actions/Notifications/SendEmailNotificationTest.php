<?php

declare(strict_types=1);

use App\Actions\Notifications\SendEmailNotification;
use App\Mail\NewVideoMail;
use App\Models\Channel;
use App\Models\Video;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;

beforeEach(function (): void {
    Channel::truncate();
    Video::truncate();
    Mail::fake();
});

describe('SendEmailNotification', function (): void {
    it('sends an email notification with video and channel data', function (): void {
        Config::set('app.alert_emails', 'email@example.com');

        $channel = Channel::factory()->create([
            'name' => 'Test Channel',
            'channel_id' => 'UC_x5XG1OV2P6uZZ5FSM9Ttw',
        ]);

        $video = Video::factory()->create([
            'title' => 'Test Video',
            'video_id' => '5ltAy1W6k-Q',
            'channel_id' => $channel->id,
        ]);

        $action = new SendEmailNotification;
        $action->execute($video);

        Mail::assertSent(NewVideoMail::class, function ($mail) use ($video, $channel) {
            return $mail->hasTo('email@example.com') &&
                $mail->video->id === $video->id &&
                $mail->channel->id === $channel->id;
        });
    });

    it('sends to multiple recipients when configured with an array', function (): void {
        Config::set('app.alert_emails', ['email1@example.com', 'email2@example.com']);

        $channel = Channel::factory()->create([
            'name' => 'Test Channel',
            'channel_id' => 'UC_x5XG1OV2P6uZZ5FSM9Ttw',
        ]);

        $video = Video::factory()->create([
            'title' => 'Test Video',
            'video_id' => '5ltAy1W6k-Q',
            'channel_id' => $channel->id,
        ]);

        $action = new SendEmailNotification;
        $action->execute($video);

        Mail::assertSent(NewVideoMail::class, function ($mail) {
            return $mail->hasTo('email1@example.com') &&
                $mail->hasTo('email2@example.com');
        });
    });
});
