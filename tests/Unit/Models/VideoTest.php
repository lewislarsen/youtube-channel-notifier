<?php

declare(strict_types=1);

use App\Models\Video;
use Illuminate\Support\Carbon;

beforeEach(function (): void {
    $this->originalTimezone = config('app.timezone');
    config(['app.timezone' => 'UTC']);
    Date::setTestNow(now('UTC'));
    Carbon::setTestNow();
});

afterEach(function (): void {
    config(['app.timezone' => $this->originalTimezone]);
    Carbon::setTestNow();
});

it('returns the full youtube link', function (): void {
    $videoId = '1234566789';
    $video = Video::factory()->create(['video_id' => $videoId]);

    expect($video->getYoutubeUrl())->toBe("https://www.youtube.com/watch?v={$videoId}");
});

it('returns thumbnail urls with different qualities', function (): void {
    $videoId = 'abc12345';
    $video = Video::factory()->create(['video_id' => $videoId]);

    expect($video->getThumbnailUrl())
        ->toBe("https://i.ytimg.com/vi/{$videoId}/hqdefault.jpg")
        ->and($video->getThumbnailUrl('default'))
        ->toBe("https://i.ytimg.com/vi/{$videoId}/default.jpg")
        ->and($video->getThumbnailUrl('mqdefault'))
        ->toBe("https://i.ytimg.com/vi/{$videoId}/mqdefault.jpg")
        ->and($video->getThumbnailUrl('maxresdefault'))
        ->toBe("https://i.ytimg.com/vi/{$videoId}/maxresdefault.jpg");
});

it('formats published date for human-readable display', function (): void {
    $publishDate = Carbon::create(2023, 5, 15, 14, 30, 0, 'UTC');
    $video = Video::factory()->create([
        'published_at' => $publishDate,
    ]);

    expect($video->getFormattedPublishedDate())
        ->toBe('15 May 2023 02:30 PM');
});

it('formats published date for human-readable display with Europe/London timezone during BST period', function (): void {
    $publishDate = Carbon::create(2023, 7, 20, 14, 30, 0, 'UTC');
    $video = Video::factory()->create([
        'published_at' => $publishDate,
    ]);

    config(['app.user_timezone' => 'Europe/London']);
    $video->refresh();

    $londonBstFormattedDate = $video->getFormattedPublishedDate();

    expect($londonBstFormattedDate)
        ->toBe('20 Jul 2023 03:30 PM');
});

it('formats published date for human-readable display with Europe/London timezone during GMT period', function (): void {
    $publishDate = Carbon::create(2023, 1, 15, 14, 30, 0, 'UTC');
    $video = Video::factory()->create([
        'published_at' => $publishDate,
    ]);

    config(['app.user_timezone' => 'Europe/London']);
    $video->refresh();
    $londonGmtFormattedDate = $video->getFormattedPublishedDate();

    expect($londonGmtFormattedDate)
        ->toBe('15 Jan 2023 02:30 PM');
});

it('formats published date as ISO8601 according to app timezone configuration', function (): void {
    $publishDate = Carbon::create(2023, 5, 15, 14, 30, 0, 'UTC');
    $video = Video::factory()->create([
        'published_at' => $publishDate,
    ]);

    $defaultIsoDate = $video->getIsoPublishedDate();
    config(['app.user_timezone' => 'America/New_York']);
    $video->refresh();
    $newTimezoneIsoDate = $video->getIsoPublishedDate();

    expect($newTimezoneIsoDate)
        ->not->toBe($defaultIsoDate)
        ->and($newTimezoneIsoDate)
        ->toBe('2023-05-15T10:30:00-04:00');
});

it('formats published date according to app timezone configuration', function (): void {
    $publishDate = Carbon::create(2023, 5, 15, 14, 30, 0, 'UTC');
    $video = Video::factory()->create([
        'published_at' => $publishDate,
    ]);

    $defaultFormattedDate = $video->getFormattedPublishedDate();

    config(['app.user_timezone' => 'America/New_York']);
    $video->refresh();
    $newTimezoneFormattedDate = $video->getFormattedPublishedDate();

    expect($newTimezoneFormattedDate)
        ->not->toBe($defaultFormattedDate)
        ->and($newTimezoneFormattedDate)
        ->toBe('15 May 2023 10:30 AM');
});

it('formats published date as ISO8601 for Discord', function (): void {

    $publishDate = Carbon::create(2023, 5, 15, 14, 30, 0, 'UTC');
    $video = Video::factory()->create([
        'published_at' => $publishDate,
    ]);

    expect($video->getIsoPublishedDate())
        ->toBe($publishDate->toIso8601String());
});

it('marks the videos notification column as true', function (): void {
    $video = Video::factory()->create(['notified_at' => null]);

    expect($video->markAsNotified())->toBeTrue()
        ->and($video->notified_at)->toBeInstanceOf(Carbon::class);
});

it('returns true if the video has been notified', function (): void {
    $video = Video::factory()->create(['notified_at' => Carbon::now()]);

    expect($video->isNotified())->toBeTrue();
});

it('returns false if the video has not been notified', function (): void {
    $video = Video::factory()->create(['notified_at' => null]);

    expect($video->isNotified())->toBeFalse();
});
