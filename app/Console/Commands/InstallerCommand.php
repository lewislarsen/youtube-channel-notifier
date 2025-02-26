<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class InstallerCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:install {--force : Force the operation to run when in production}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install and configure the YouTube Channel Notifier';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        // Show a warning when in production
        if (App::environment('production') && ! $this->option('force')) {
            $this->components->error('You are running the installer in production environment!');
            $this->newLine();

            $this->components->warn(
                'Running the installer in production may overwrite your existing configuration.'
            );

            $this->components->bulletList([
                'Your existing environment configuration might be overwritten',
                'Database migrations will be run which could affect your data',
                'A new application key will be generated which affects encrypted values',
            ]);

            $this->newLine();

            if (! $this->components->confirm('Do you really wish to proceed?', false)) {
                $this->components->info('Command canceled.');

                return 1;
            }

            if (! $this->components->confirm('Are you sure? This cannot be undone', false)) {
                $this->components->info('Command canceled.');

                return 1;
            }

            $this->newLine();
            $this->components->info('Continuing installation in production environment...');
            $this->newLine();
        }

        $this->components->info('Welcome to the YouTube Channel Notifier installer!');
        $this->components->info('This command will help you set up the application.');
        $this->newLine();

        // Check if .env already exists
        if (File::exists(base_path('.env'))) {

            if (! $this->confirm('An .env file already exists. Do you want to overwrite it?', false)) {
                $this->components->info('Installation aborted. Your existing .env file was not modified.');

                return 1;
            }
        }

        // Create .env file from .env.example
        if (! File::exists(base_path('.env.example'))) {
            $this->components->error('.env.example file not found. Please make sure the file exists before running the installer.');

            return 1;
        }

        // Copy .env.example to .env
        File::copy(base_path('.env.example'), base_path('.env'));
        $this->components->info('Created .env file.');

        // Collect user input for configuration
        $this->configureEnvironment();

        // Generate application key
        $this->components->task('Generating application key', function () {
            Artisan::call('key:generate', ['--force' => true]);

            return true;
        });

        // Set up database
        $this->components->task('Setting up the database', function () {
            if (! File::exists(database_path('database.sqlite'))) {
                File::put(database_path('database.sqlite'), '');
            }

            return true;
        });

        // Run migrations
        $this->components->task('Running database migrations', function () {
            Artisan::call('migrate', ['--force' => true]);

            return true;
        });

        $this->newLine();
        $this->components->success('Installation completed successfully!');
        $this->newLine();

        $this->showNextSteps();

        return 0;
    }

    /**
     * Configure the .env file with user input.
     */
    private function configureEnvironment(): void
    {
        $this->newLine();
        $this->components->info('Let\'s configure your notification settings:');
        $this->newLine();

        // Configure alert emails
        $alertEmails = $this->ask('Enter email address(es) for notifications (comma-separated for multiple)');
        $this->updateEnv('ALERT_EMAIL', $alertEmails);
        $this->components->task('Setting up email notifications', fn () => true);

        // Configure email settings
        if ($this->confirm('Do you want to configure SMTP for sending emails?', true)) {
            $mailDriver = 'smtp';
            $mailHost = $this->ask('SMTP Host', 'smtp.gmail.com');
            $mailPort = $this->ask('SMTP Port', '587');
            $mailUsername = $this->ask('SMTP Username');
            $mailPassword = $this->secret('SMTP Password');
            $mailEncryption = $this->choice('SMTP Encryption', ['tls', 'ssl', 'none'], 0);
            $mailFromAddress = $this->ask('From Email Address', $mailUsername);
            $mailFromName = $this->ask('From Name', 'YouTube Channel Notifier');

            if ($mailEncryption === 'none') {
                $mailEncryption = null;
            }

            $this->updateEnv('MAIL_MAILER', $mailDriver);
            $this->updateEnv('MAIL_HOST', $mailHost);
            $this->updateEnv('MAIL_PORT', $mailPort);
            $this->updateEnv('MAIL_USERNAME', $mailUsername);
            $this->updateEnv('MAIL_PASSWORD', $mailPassword);
            $this->updateEnv('MAIL_ENCRYPTION', $mailEncryption);
            $this->updateEnv('MAIL_FROM_ADDRESS', $mailFromAddress);
            $this->updateEnv('MAIL_FROM_NAME', $mailFromName);

            $this->components->task('Configuring SMTP settings', fn () => true);
        } else {
            $this->components->warn('Using log driver for emails. Emails will be written to the log file.');
            $this->updateEnv('MAIL_MAILER', 'log');
        }

        // Configure Discord webhook
        if ($this->confirm('Do you want to set up Discord notifications?', false)) {
            $webhookUrl = $this->ask('Enter your Discord webhook URL');
            $this->updateEnv('DISCORD_WEBHOOK_URL', $webhookUrl);
            $this->components->task('Setting up Discord notifications', fn () => true);
        }

        // Set a sensible default for logging
        $this->updateEnv('LOG_LEVEL', 'info');

        $this->newLine();
        $this->components->info('Environment configured successfully!');
    }

    /**
     * Update the .env file with new values.
     */
    private function updateEnv(string $key, ?string $value): void
    {
        if (is_null($value)) {
            $value = 'null';
        } else {
            // Escape quotes
            $value = str_replace('"', '\"', $value);

            // Wrap in quotes if value contains spaces or special characters
            if (Str::contains($value, ' ') || Str::contains($value, '#') || Str::contains($value, '=')) {
                $value = '"'.$value.'"';
            }
        }

        $envFile = base_path('.env');
        $content = File::get($envFile);

        // Check if key exists
        if (preg_match("/^{$key}=.*$/m", $content)) {
            // Replace existing value
            $content = preg_replace("/^{$key}=.*$/m", "{$key}={$value}", $content);
        } else {
            // Add new key-value pair
            $content .= "\n{$key}={$value}";
        }

        File::put($envFile, $content);
    }

    /**
     * Show next steps to the user.
     */
    private function showNextSteps(): void
    {
        $this->components->info('Next steps:');
        $this->newLine();

        $this->components->bulletList([
            'Add your first YouTube channel: <fg=yellow>php artisan channels:add</>',
            'Set up the scheduler: <fg=yellow>* * * * * cd '.base_path().' && php artisan schedule:run >> /dev/null 2>&1</>',
            'Test the system by running: <fg=yellow>php artisan channels:check</>',
            'List your monitored channels: <fg=yellow>php artisan channels:list</>',
        ]);

        $this->newLine();
        $this->components->info('Thank you for installing YouTube Channel Notifier!');

        $this->newLine(2);
        $this->components->twoColumnDetail(
            '<fg=bright-blue>★ Enjoying this tool?</>',
            'Please consider starring the project on GitHub: <fg=green>https://github.com/lewislarsen/youtube-channel-notifier</>'
        );
        $this->newLine();
    }
}
