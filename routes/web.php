<?php

declare(strict_types=1);

use App\Actions\Summaries\FetchRecentVideosForSummary;
use App\Mail\NewVideoMail;
use App\Mail\WeeklySummaryMail;
use App\Models\Channel;
use App\Models\Video;
use Illuminate\Support\Facades\Route;

Route::view('/', 'index')->name('index');

Route::get('/mail', function () {
    if (! Config::get('app.debug')) {
        abort(404);
    }

    $channel = Channel::factory()->create();
    $video = Video::factory()->create([
        'channel_id' => $channel->id,
    ]);

    return (new NewVideoMail($video, $channel))->render();
});

Route::get('/mail/weekly-summary', function (FetchRecentVideosForSummary $fetchRecentVideosForSummary) {
    if (! Config::get('app.debug')) {
        abort(404);
    }

    $videos = collect();

    $mondayVideos = Video::factory()->count(3)->create([
        'created_at' => now()->subWeek()->startOfWeek(), // Monday
        'notified_at' => now()->subWeek()->startOfWeek()->addHour(),
    ]);
    $videos = $videos->merge($mondayVideos);

    $tuesdayVideos = Video::factory()->count(4)->create([
        'created_at' => now()->subWeek()->startOfWeek()->addDay(), // Tuesday
        'notified_at' => now()->subWeek()->startOfWeek()->addDay()->addHour(),
    ]);
    $videos = $videos->merge($tuesdayVideos);

    $wednesdayVideos = Video::factory()->count(5)->create([
        'created_at' => now()->subWeek()->startOfWeek()->addDays(2), // Wednesday
        'notified_at' => now()->subWeek()->startOfWeek()->addDays(2)->addHour(),
    ]);
    $videos = $videos->merge($wednesdayVideos);

    $thursdayVideos = Video::factory()->count(3)->create([
        'created_at' => now()->subWeek()->startOfWeek()->addDays(3), // Thursday
        'notified_at' => now()->subWeek()->startOfWeek()->addDays(3)->addHour(),
    ]);
    $videos = $videos->merge($thursdayVideos);

    $fridayVideos = Video::factory()->count(2)->create([
        'created_at' => now()->subWeek()->startOfWeek()->addDays(4), // Friday
        'notified_at' => now()->subWeek()->startOfWeek()->addDays(4)->addHour(),
    ]);
    $videos = $videos->merge($fridayVideos);

    $saturdayVideos = Video::factory()->count(1)->create([
        'created_at' => now()->subWeek()->startOfWeek()->addDays(5), // Saturday
        'notified_at' => now()->subWeek()->startOfWeek()->addDays(5)->addHour(),
    ]);
    $videos = $videos->merge($saturdayVideos);

    $sundayVideos = Video::factory()->count(3)->create([
        'created_at' => now()->subWeek()->startOfWeek()->addDays(6), // Sunday
        'notified_at' => now()->subWeek()->startOfWeek()->addDays(6)->addHour(),
    ]);
    $videos = $videos->merge($sundayVideos);

    $weekdays = $fetchRecentVideosForSummary->executeWithVideos($videos);

    return (new WeeklySummaryMail($weekdays))->render();
});
