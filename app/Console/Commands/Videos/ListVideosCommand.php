<?php

declare(strict_types=1);

namespace App\Console\Commands\Videos;

use App\Models\Video;
use Carbon\Carbon;
use Illuminate\Console\Command;

class ListVideosCommand extends Command
{
    protected $signature = 'videos:list';

    protected $description = 'Lists all the videos that have been stored in the database';

    public function handle(): void
    {
        $videos = Video::orderBy('published_at', 'desc')->with('channel')->get();

        $this->table(
            ['Title', 'Creator', 'Published', 'URL'],
            $videos->map(function (Video $video): array {
                return [
                    $video->title,
                    $video->channel->name ?? 'Unknown',
                    Carbon::parse($video->published_at)->diffForHumans(),
                    $video->getYoutubeUrl(),
                ];
            })->toArray()
        );
    }
}
