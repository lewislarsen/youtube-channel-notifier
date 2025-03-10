<?php

declare(strict_types=1);

namespace App\Providers;

use App\Console\Commands\InstallerCommand;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                InstallerCommand::class,
            ]);
        }
    }
}
