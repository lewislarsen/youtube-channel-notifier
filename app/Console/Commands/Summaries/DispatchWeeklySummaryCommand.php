<?php

declare(strict_types=1);

namespace App\Console\Commands\Summaries;

use App\Actions\Summaries\FetchRecentVideosForSummary;
use App\Mail\WeeklySummaryMail;
use App\Models\Video;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;

class DispatchWeeklySummaryCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'summaries:dispatch-weekly-summary';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Triggers the dispatch of the weekly summary email.';

    public function __construct(
        private readonly FetchRecentVideosForSummary $fetchRecentVideosForSummary
    ) {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        if (! Config::get('app.dispatch_weekly_summary_email', true)) {
            $this->components->warn('Weekly summary email dispatch is disabled.');

            return;
        }

        if (empty(Config::get('app.alert_emails'))) {
            $this->components->error('No email addresses configured.');

            return;
        }

        $weekdays = $this->fetchRecentVideosForSummary->execute();

        if (empty($weekdays)) {
            $this->components->info('No new uploads found for weekdays in the past week.');

            return;
        }

        $totalVideos = collect($weekdays)->sum(function (array $day): int {
            /** @var Collection<int, Video> $videos */
            $videos = $day['videos'];

            return $videos->count();
        });

        $this->info("Found {$totalVideos} videos across ".count($weekdays).' weekdays for the weekly summary.');

        Mail::to(Config::get('app.alert_emails'))->send(new WeeklySummaryMail($weekdays));

        $this->components->success('Weekly summary email dispatched successfully.');
    }
}
