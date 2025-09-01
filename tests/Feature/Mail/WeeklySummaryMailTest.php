<?php

declare(strict_types=1);

use App\Actions\Summaries\FetchRecentVideosForSummary;
use App\Mail\WeeklySummaryMail;
use App\Models\Channel;
use App\Models\Video;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

it('builds the mail correctly', function (): void {
    $creator1 = Channel::factory()->create(['name' => 'Creator One']);
    $creator2 = Channel::factory()->create(['name' => 'Creator Two']);

    $video1 = Video::factory()->create([
        'channel_id' => $creator1->id,
        'created_at' => now()->subWeek()->startOfWeek()->addHours(10), // Monday of last week
        'notified_at' => now()->subWeek()->startOfWeek()->addHours(11),
    ]);

    $video2 = Video::factory()->create([
        'channel_id' => $creator2->id,
        'created_at' => now()->subWeek()->startOfWeek()->addDays(1)->addHours(14), // Tuesday of last week
        'notified_at' => now()->subWeek()->startOfWeek()->addDays(1)->addHours(15),
    ]);

    $video3 = Video::factory()->create([
        'channel_id' => $creator1->id,
        'created_at' => now()->subWeek()->startOfWeek()->addDays(2)->addHours(9), // Wednesday of last week
        'notified_at' => now()->subWeek()->startOfWeek()->addDays(2)->addHours(10),
    ]);

    $videos = new EloquentCollection([$video1->load('channel'), $video2->load('channel'), $video3->load('channel')]);

    $fetchAction = new FetchRecentVideosForSummary;
    $weekdays = $fetchAction->executeWithVideos($videos);

    $mailable = new WeeklySummaryMail($weekdays);

    $mailable->assertHasSubject(__('email.weekly_summary_subject'));
    $mailable->assertSeeInText(__('email.weekly_summary_intro'));
    $mailable->assertSeeInText('Creator One');
    $mailable->assertSeeInText('Creator Two');
    $mailable->assertSeeInText($video1->title);
    $mailable->assertSeeInText($video2->title);
    $mailable->assertSeeInText($video3->title);
    $mailable->assertSeeInText(__('email.weekday_monday'));
    $mailable->assertSeeInText(__('email.weekday_tuesday'));
    $mailable->assertSeeInText(__('email.weekday_wednesday'));
    $mailable->assertSeeInText(now()->subWeek()->startOfWeek()->format('M j, Y')); // Monday date
    $mailable->assertSeeInText(now()->subWeek()->startOfWeek()->addDay()->format('M j, Y')); // Tuesday date
    $mailable->assertSeeInText(now()->subWeek()->startOfWeek()->addDays(2)->format('M j, Y')); // Wednesday date
});
