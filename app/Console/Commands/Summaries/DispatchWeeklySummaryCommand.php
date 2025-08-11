<?php

declare(strict_types=1);

namespace App\Console\Commands\Summaries;

use App\Mail\WeeklySummaryMail;
use App\Models\Video;
use Illuminate\Console\Command;
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

        $lastWeeksVideos = Video::where('created_at', '>=', now()->subWeek())
            ->whereNotNull('notified_at') // Only include videos that we've notified the user about
            ->orderBy('created_at', 'desc')
            ->with('channel')
            ->get();

        if ($lastWeeksVideos->isEmpty()) {
            $this->components->info('No new uploads found for the past week.');

            return;
        }

        $this->info("Found {$lastWeeksVideos->count()} videos for the weekly summary.");

        Mail::to(Config::get('app.alert_emails'))->send(new WeeklySummaryMail($lastWeeksVideos));

        $this->components->success('Weekly summary email dispatched successfully.');
    }
}
