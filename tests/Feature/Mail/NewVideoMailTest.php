<?php

use App\Mail\NewVideoMail;
use App\Models\Channel;
use App\Models\Video;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;

it('builds the mailable correctly', function () {
    $channel = Channel::factory()->create(['name' => 'TestChannel']);
    $video = Video::factory()->make([
        'channel_id' => $channel->id,
        'title' => 'Test Video',
        'video_id' => '5ltAy1W6k-Q',
        'published_at' => Carbon::now(),
    ]);

    $mailable = new NewVideoMail($video);

    expect($mailable->envelope()->subject)
        ->toBe('TestChannel - New Video: Test Video');

    $content = $mailable->content();

    expect($content->markdown)->toBe('mail.new-video-mail')
        ->and($content->with['videoCreator'])->toBe('TestChannel')
        ->and($content->with['videoTitle'])->toBe('Test Video')
        ->and($content->with['videoUrl'])->toBe('https://www.youtube.com/watch?v=5ltAy1W6k-Q')
        ->and($content->with['published'])->toBe($video->published_at->format('d M Y h:i A'));
});

it('sends the mailable', function () {
    Mail::fake();

    $channel = Channel::factory()->create(['name' => 'TestChannel']);
    $video = Video::factory()->make([
        'channel_id' => $channel->id,
        'title' => 'Test Video',
        'video_id' => '5ltAy1W6k-Q',
        'published_at' => Carbon::now(),
    ]);

    Mail::to('lewis@larsens.dev')->send(new NewVideoMail($video));

    Mail::assertSent(NewVideoMail::class, function ($mail) use ($video) {
        return $mail->video->is($video) && $mail->hasTo('lewis@larsens.dev');
    });
});
