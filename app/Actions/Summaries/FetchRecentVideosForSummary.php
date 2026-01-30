<?php

declare(strict_types=1);

namespace App\Actions\Summaries;

use App\Models\Video;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;

class FetchRecentVideosForSummary
{
    /**
     * Fetch and group videos from the past week by days.
     *
     * @return array<string, array{date: Carbon, videos: EloquentCollection<int, Video>}>
     */
    public function execute(): array
    {
        $startOfLastWeek = now()->subWeek()->startOfWeek();
        $endOfLastWeek = $startOfLastWeek->copy()->endOfWeek();

        $lastWeeksVideos = Video::whereBetween('created_at', [$startOfLastWeek, $endOfLastWeek])
            ->whereNotNull('notified_at')
            ->orderBy('created_at', 'asc')
            ->with('channel')
            ->get();

        if ($lastWeeksVideos->isEmpty()) {
            return [];
        }

        return $this->groupVideosByDays($lastWeeksVideos);
    }

    /**
     * Fetch and group a specific collection of videos by days.
     * Useful for testing with custom video collections.
     *
     * @param  Collection<int, Video>|EloquentCollection<int, Video>  $videos
     * @return array<string, array{date: Carbon, videos: EloquentCollection<int, Video>}>
     */
    public function executeWithVideos(EloquentCollection|Collection $videos): array
    {
        if ($videos->isEmpty()) {
            return [];
        }

        $videoCollection = $videos instanceof EloquentCollection
            ? $videos
            : new EloquentCollection($videos->all());

        return $this->groupVideosByDays($videoCollection);
    }

    /**
     * Group videos by days (all days of the week).
     *
     * @param  EloquentCollection<int, Video>  $eloquentCollection
     * @return array<string, array{date: Carbon, videos: EloquentCollection<int, Video>}>
     */
    private function groupVideosByDays(EloquentCollection $eloquentCollection): array
    {
        $groupedVideos = $eloquentCollection->groupBy(function (Video $video): string {
            return \Illuminate\Support\Facades\Date::parse($video->created_at)->format('Y-m-d');
        });

        $days = [];
        $startOfWeek = now()->subWeek()->startOfWeek(); // Monday of last week

        for ($i = 0; $i < 7; $i++) { // This is Monday to Sunday
            $date = $startOfWeek->copy()->addDays($i);
            $dateString = $date->format('Y-m-d');

            if ($groupedVideos->has($dateString)) {
                $videoCollection = $groupedVideos->get($dateString);
                if ($videoCollection !== null && $videoCollection->isNotEmpty()) {
                    $days[$dateString] = [
                        'date' => $date,
                        'videos' => $videoCollection,
                    ];
                }
            }
        }

        return $days;
    }
}
