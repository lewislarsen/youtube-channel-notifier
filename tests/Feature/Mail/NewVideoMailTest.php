<?php

use App\Mail\NewVideoMail;
use App\Models\Video;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;

it('builds the NewVideoMail mailable correctly', function () {
    $video = Video::factory()->make([
        'title' => 'Test Video',
        'video_id' => '5ltAy1W6k-Q',
        'published_at' => Carbon::now(),
    ]);

    $mailable = new NewVideoMail($video);

    expect($mailable->envelope()->subject)->toBe('New Video Uploaded: Test Video');

    // Assert that the content is correctly built
    $content = $mailable->content();
    expect($content->markdown)->toBe('mail.new-video-mail')
        ->and($content->with['videoTitle'])->toBe('Test Video')
        ->and($content->with['videoUrl'])->toBe('https://www.youtube.com/watch?v=5ltAy1W6k-Q')
        ->and($content->with['publishedAt'])->toBe($video->published_at->toFormattedDateString());
});

it('sends the NewVideoMail mailable', function () {
    Mail::fake();

    $video = Video::factory()->make([
        'title' => 'Test Video',
        'video_id' => '5ltAy1W6k-Q',
        'published_at' => Carbon::now(),
    ]);

    Mail::to('lewis@larsens.dev')->send(new NewVideoMail($video));

    Mail::assertSent(NewVideoMail::class, function ($mail) use ($video) {
        return $mail->video->is($video) && $mail->hasTo('lewis@larsens.dev');
    });
});
