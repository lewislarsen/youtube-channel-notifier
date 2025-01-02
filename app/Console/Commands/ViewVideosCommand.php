<?php

namespace App\Console\Commands;

use App\Models\Video;
use Carbon\Carbon;
use Illuminate\Console\Command;

class ViewVideosCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'videos:list';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Lists all the videos that have been stored in the database,';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $videos = Video::orderBy('published_at', 'desc')->get();

        $this->table(
            ['Title', 'Creator', 'Published'],
            $videos->map(function (Video $video) {
                return [
                    $video->title,
                    $video->channel->name,
                    Carbon::parse($video->published_at)->diffForHumans(),
                ];
            })->toArray()
        );
    }
}
