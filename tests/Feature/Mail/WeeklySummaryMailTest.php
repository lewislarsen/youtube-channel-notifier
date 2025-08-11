<?php

declare(strict_types=1);

use App\Mail\WeeklySummaryMail;
use App\Models\Channel;
use App\Models\Video;

it('builds the mail correctly', function (): void {
    $creator1 = Channel::factory()->create(['name' => 'Creator One']);
    $creator2 = Channel::factory()->create(['name' => 'Creator Two']);

    $video1 = Video::factory()->create([
        'channel_id' => $creator1->id,
        'created_at' => now()->startOfWeek()->addHours(10), // Monday
    ]);

    $video2 = Video::factory()->create([
        'channel_id' => $creator2->id,
        'created_at' => now()->startOfWeek()->addDays(1)->addHours(14), // Tuesday
    ]);

    $video3 = Video::factory()->create([
        'channel_id' => $creator1->id,
        'created_at' => now()->startOfWeek()->addDays(2)->addHours(9), // Wednesday
    ]);

    $videos = collect([$video1->load('channel'), $video2->load('channel'), $video3->load('channel')]);

    $mailable = new WeeklySummaryMail($videos);

    $mailable->assertHasSubject(__('email.weekly_summary_subject'));
    $mailable->assertSeeInText(__('email.weekly_summary_intro'));

    $mailable->assertSeeInText('Creator One');
    $mailable->assertSeeInText('Creator Two');

    // Check that all video titles appear in the email
    $mailable->assertSeeInText($video1->title);
    $mailable->assertSeeInText($video2->title);
    $mailable->assertSeeInText($video3->title);
});
