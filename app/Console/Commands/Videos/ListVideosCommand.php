<?php

declare(strict_types=1);

namespace App\Console\Commands\Videos;

use App\Models\Video;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Date;

class ListVideosCommand extends Command
{
    protected $signature = 'videos:list {--notified : Only list videos that have not been notified}';

    protected $description = 'Lists all the videos that have been stored in the database';

    public function handle(): void
    {
        $builder = Video::with('channel')->latest('published_at');

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
                    Date::parse($video->published_at)->setTimezone(config('app.user_timezone'))->diffForHumans(),
                    $video->notified_at?->setTimezone(config('app.user_timezone'))->diffForHumans() ?? '—',
                    $video->getYoutubeUrl(),
                ];
            })->toArray()
        );
    }
}
