<?php

use App\Console\Commands\CheckChannelsCommand;
use Illuminate\Support\Facades\Schedule;

Schedule::command(CheckChannelsCommand::class)
    ->hourly();
