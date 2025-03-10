<?php

declare(strict_types=1);

use App\Models\Video;
use Illuminate\Support\Carbon;

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
    // Create a video with a fixed publish date
    $publishDate = Carbon::create(2023, 5, 15, 14, 30, 0);
    $video = Video::factory()->create([
        'published_at' => $publishDate,
    ]);

    // Verify the formatted date matches expected format (15 May 2023 02:30 PM)
    expect($video->getFormattedPublishedDate())
        ->toBe($publishDate->format('d M Y h:i A'));
});

it('formats published date as ISO8601 for Discord', function (): void {
    // Create a video with a fixed publish date
    $publishDate = Carbon::create(2023, 5, 15, 14, 30, 0);
    $video = Video::factory()->create([
        'published_at' => $publishDate,
    ]);

    // Verify the ISO formatted date matches expected format
    expect($video->getIsoPublishedDate())
        ->toBe($publishDate->toIso8601String());
});
