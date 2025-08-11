<?php

declare(strict_types=1);

use App\Console\Commands\Channels\CheckChannelsCommand;
use App\Console\Commands\Summaries\DispatchWeeklySummaryCommand;
use Illuminate\Support\Facades\Schedule;

Schedule::command(CheckChannelsCommand::class)
    ->everyFiveMinutes();

Schedule::command(DispatchWeeklySummaryCommand::class)
    ->weeklyOn(1, '00:00');
