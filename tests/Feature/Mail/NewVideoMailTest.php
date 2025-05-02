<?php

declare(strict_types=1);

use App\Mail\NewVideoMail;
use App\Models\Channel;
use App\Models\Video;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;

it('builds the mailable correctly', function (): void {
    $channel = Channel::factory()->create(['name' => 'TestChannel']);
    $video = Video::factory()->make([
        'channel_id' => $channel->id,
        'title' => 'Test Video',
        'video_id' => '5ltAy1W6k-Q',
        'published_at' => Carbon::now(),
    ]);

    $mailable = new NewVideoMail($video, $channel);

    expect($mailable->envelope()->subject)
        ->toBe('New Upload: TestChannel - "Test Video"');

    $content = $mailable->content();

    expect($content->markdown)->toBe('mail.new-video-mail')
        ->and($content->with['videoCreator'])->toBe('TestChannel')
        ->and($content->with['videoTitle'])->toBe('Test Video')
        ->and($content->with['videoUrl'])->toBe('https://www.youtube.com/watch?v=5ltAy1W6k-Q')
        ->and($content->with['published'])->toBe($video->published_at->format('d M Y h:i A'))
        ->and($content->with['thumbnailUrl'])->toBe('https://i.ytimg.com/vi/5ltAy1W6k-Q/maxresdefault.jpg');
});

it('sends the mailable to a single email address', function (): void {
    Config::set('app.alert_emails', ['lewis@larsens.dev']);
    Mail::fake();

    $channel = Channel::factory()->create(['name' => 'TestChannel']);
    $video = Video::factory()->make([
        'channel_id' => $channel->id,
        'title' => 'Test Video',
        'video_id' => '5ltAy1W6k-Q',
        'published_at' => Carbon::now(),
    ]);

    Mail::to(Config::get('app.alert_emails'))->send(new NewVideoMail($video, $channel));

    Mail::assertSent(NewVideoMail::class, function ($mail) use ($video) {
        return $mail->video->is($video) && $mail->hasTo('lewis@larsens.dev');
    });
});

it('sends the mailable to multiple email addresses', function (): void {
    Config::set('app.alert_emails', ['lewis@larsens.dev', 'another@example.com']);
    Mail::fake();

    $channel = Channel::factory()->create(['name' => 'TestChannel']);
    $video = Video::factory()->make([
        'channel_id' => $channel->id,
        'title' => 'Test Video',
        'video_id' => '5ltAy1W6k-Q',
        'published_at' => Carbon::now(),
    ]);

    Mail::to(Config::get('app.alert_emails'))->send(new NewVideoMail($video, $channel));

    Mail::assertSent(NewVideoMail::class, function ($mail) use ($video) {
        return $mail->video->is($video)
            && $mail->hasTo('lewis@larsens.dev')
            && $mail->hasTo('another@example.com');
    });
});
