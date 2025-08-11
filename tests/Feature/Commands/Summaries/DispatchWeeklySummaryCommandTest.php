<?php

declare(strict_types=1);

use App\Console\Commands\Summaries\DispatchWeeklySummaryCommand;
use App\Mail\WeeklySummaryMail;
use App\Models\Video;

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
        ->expectsOutputToContain('No new uploads found for the past week.')
        ->assertExitCode(0);

    expect(Video::where('created_at', '>=', now()->subWeek())->count())->toBe(0);
});

it('sends an email containing the last weeks emails', function (): void {
    Config::set('app.dispatch_weekly_summary_email', true);
    Config::set('app.alert_emails', ['user@email.com']);
    Mail::fake();

    // We're only sending emails that the user have been notified about
    // We import all available videos from newly added creators
    // so we could flood this email if handled incorrectly
    Video::factory()->count(5)->create([
        'notified_at' => now()->subDays(2),
        'created_at' => now()->subDays(3), // Ensure these are within the last week
    ]);

    $this->artisan(DispatchWeeklySummaryCommand::class)
        ->expectsOutputToContain('Weekly summary email dispatched successfully.')
        ->assertExitCode(0);

    Mail::assertSent(WeeklySummaryMail::class);

    expect(Video::where('created_at', '>=', now()->subWeek())->count())->toBe(5)
        ->and(Video::where('created_at', '<', now()->subWeek())->count())->toBe(0)
        ->and(Video::where('notified_at', '>=', now()->subWeek())->count())->toBe(5);
});
