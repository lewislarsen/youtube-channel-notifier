<?php

declare(strict_types=1);

use App\Console\Commands\CheckChannelsCommand;
use Illuminate\Support\Facades\Schedule;

Schedule::command(CheckChannelsCommand::class)
    ->everyFiveMinutes();
