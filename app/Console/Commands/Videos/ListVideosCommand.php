<?php

declare(strict_types=1);

namespace App\Console\Commands\Videos;

use App\Models\Video;
use Carbon\Carbon;
use Illuminate\Console\Command;

class ListVideosCommand extends Command
{
    protected $signature = 'videos:list {--notified : Only list videos that have not been notified}';

    protected $description = 'Lists all the videos that have been stored in the database';

    public function handle(): void
    {
        $builder = Video::with('channel')->orderBy('published_at', 'desc');

        if ($this->option('notified')) {
            $builder->whereNotNull('notified_at');
        }

        $videos = $builder->get();

        $this->table(
            ['Title', 'Creator', 'Published', 'Notified', 'URL'],
            $videos->map(function (Video $video): array {
                return [
                    $video->title,
                    $video->channel->name ?? 'Unknown',
                    Carbon::parse($video->published_at)->diffForHumans(),
                    $video->notified_at ? Carbon::parse($video->notified_at)->diffForHumans() : '—',
                    $video->getYoutubeUrl(),
                ];
            })->toArray()
        );
    }
}
