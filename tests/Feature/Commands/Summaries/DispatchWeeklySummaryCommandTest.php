<?php

declare(strict_types=1);

use App\Console\Commands\Summaries\DispatchWeeklySummaryCommand;
use App\Mail\WeeklySummaryMail;
use App\Models\Video;
use Illuminate\Support\Facades\Date;

it('outputs a message when weekly summary dispatch is disabled', function (): void {
    Config::set('app.dispatch_weekly_summary_email', false);

    $this->artisan(DispatchWeeklySummaryCommand::class)
        ->expectsOutputToContain('Weekly summary email dispatch is disabled.')
        ->assertExitCode(0);
});

it('outputs an error when no email addresses are configured', function (): void {
    Config::set('app.dispatch_weekly_summary_email', true);
    Config::set('app.alert_emails', []);

    $this->artisan(DispatchWeeklySummaryCommand::class)
        ->expectsOutputToContain('No email addresses configured.')
        ->assertExitCode(0);
});

it('outputs a message when no new uploads are found for the past week', function (): void {
    Config::set('app.dispatch_weekly_summary_email', true);
    Config::set('app.alert_emails', ['user@email.com']);

    Video::factory()->count(5)->create([
        'created_at' => now()->subDays(8), // Ensure these are outside the last week
    ]);

    $this->artisan(DispatchWeeklySummaryCommand::class)
        ->expectsOutputToContain('No new uploads found for weekdays in the past week.')
        ->assertExitCode(0);

    expect(Video::where('created_at', '>=', now()->subWeek())->count())->toBe(0);
});

it('sends an email containing the last weeks emails', function (): void {
    Config::set('app.dispatch_weekly_summary_email', true);
    Config::set('app.alert_emails', ['user@email.com']);
    Mail::fake();

    $now = Date::parse('2025-11-01 12:00:00');
    $this->travelTo($now);

    $startOfLastWeek = $now->copy()->subWeek()->startOfWeek(); // Oct 20 (Monday)
    $startOfLastWeek->copy()->endOfWeek(); // Oct 26 (Sunday)

    Video::factory()->count(2)->create([
        'notified_at' => $startOfLastWeek->copy()->addDays(1)->subHour(), // Ensure it's set
        'created_at' => $startOfLastWeek->copy()->addDays(1), // Oct 21 (Tue)
    ]);

    Video::factory()->count(3)->create([
        'notified_at' => $startOfLastWeek->copy()->addDays(3)->subHour(), // Ensure it's set
        'created_at' => $startOfLastWeek->copy()->addDays(3), // Oct 23 (Thu)
    ]);

    $this->artisan(DispatchWeeklySummaryCommand::class)
        ->expectsOutputToContain('Found 5 videos across 2 weekdays for the weekly summary.')
        ->expectsOutputToContain('Weekly summary email dispatched successfully.')
        ->assertExitCode(0);

    Mail::assertSent(WeeklySummaryMail::class);

    expect(Video::whereNotNull('notified_at')->count())->toBe(5);
});
