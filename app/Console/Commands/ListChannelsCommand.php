<?php

namespace App\Console\Commands;

use App\Models\Channel;
use Carbon\Carbon;
use Illuminate\Console\Command;

class ListChannelsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'channels:list';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Lists all the channels that have been stored in the database,';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $channels = Channel::orderBy('created_at', 'desc')->get();

        $this->table(
            ['Name', 'Videos Stored', 'Last Checked', 'Channel URL'],
            $channels->map(function (Channel $channel) {
                return [
                    $channel->name,
                    $channel->videos()->count(),
                    Carbon::parse($channel->last_checked_at)->diffForHumans(),
                    'https://youtube.com/channel/'.$channel->getAttributeValue('channel_id'),
                ];
            })->toArray()
        );
    }
}
