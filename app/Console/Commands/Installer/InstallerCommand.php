<?php

declare(strict_types=1);

namespace App\Console\Commands\Installer;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\password;
use function Laravel\Prompts\select;
use function Laravel\Prompts\text;

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
    public function handle(): void
    {
        $this->components->info('🎬 Welcome to the YouTube Channel Notifier!');
        $this->components->info("Let's get your notification system set up in just a few minutes.");
        $this->newLine();

        if (App::environment('production') && ! $this->option('force')) {
            $this->components->error('⚠️ Production Environment Detected!');
            $this->newLine();

            $this->components->warn(
                'Running this installer in production might affect your existing setup:'
            );

            $this->components->bulletList([
                'Your current environment settings may be overwritten',
                'Database migrations will run (potentially affecting existing data)',
                'A new application key will be generated (invalidating existing encrypted values)',
            ]);

            $this->newLine();

            if (! confirm('Would you like to proceed anyway?', false)) {
                $this->components->info('No problem! Installation canceled.');

                return;
            }

            if (! confirm('Just to be sure - this cannot be undone. Continue?', false)) {
                $this->components->info('Installation safely canceled.');

                return;
            }

            $this->newLine();
            $this->components->info('Continuing with installation in production environment...');
            $this->newLine();
        }

        if (File::exists(base_path('.env')) &&
            ! confirm('I noticed an .env file already exists. Is it okay to replace it?', false)) {
            $this->components->info('Got it! Your existing configuration has been preserved.');

            return;
        }

        if (! File::exists(base_path('.env.example'))) {
            $this->components->error("I can't find the .env.example template file. Please check that it exists before running the installer.");
        }

        File::copy(base_path('.env.example'), base_path('.env'));
        $this->components->info('✅ Created fresh .env configuration file.');

        $this->configureEnvironment();

        $this->components->task('Generating secure application key', function () {
            Artisan::call('key:generate', ['--force' => true]);

            return true;
        });

        $this->components->task('Setting up SQLite database', function () {
            if (! File::exists(database_path('database.sqlite'))) {
                File::put(database_path('database.sqlite'), '');
            }

            return true;
        });

        $this->components->task('Running database migrations', function () {
            Artisan::call('migrate', ['--force' => true]);

            return true;
        });

        $this->newLine();
        $this->components->success('🎉 Installation completed successfully!');
        $this->components->info('Your YouTube Channel Notifier is now ready to use.');
        $this->newLine();

        $this->showNextSteps();
    }

    /**
     * Configure the .env file with user input.
     */
    private function configureEnvironment(): void
    {
        $this->newLine();
        $this->components->info("📝 Let's personalize your notification settings:");
        $this->newLine();

        $alertEmails = text('Where should notifications be sent? (Email addresses, comma-separated for multiple)');
        $this->updateEnv('ALERT_EMAILS', $alertEmails);
        $this->components->task('Setting up email notification recipients', fn () => true);

        if (confirm('Would you like to configure SMTP for sending emails? (Recommended)', true)) {
            $mailHost = text('SMTP Host', 'smtp.gmail.com');
            $mailPort = text('SMTP Port', '587');
            $mailUsername = text('SMTP Username (usually your email address)');
            $mailPassword = password('SMTP Password (input will be hidden)');
            $mailEncryption = (string) select(
                label: 'SMTP Encryption Type',
                options: ['tls', 'ssl', 'none'],
                default: 'tls',
                hint: 'This is usually tls.');
            $mailFromAddress = text(label: 'From Email Address',
                default: $mailUsername,
                required: true,
                hint: 'The email address that notifications will be sent from.'
            );

            if ($mailEncryption === 'none') {
                $mailEncryption = null;
            }

            $this->updateEnv('MAIL_MAILER', 'smtp');
            $this->updateEnv('MAIL_HOST', $mailHost);
            $this->updateEnv('MAIL_PORT', $mailPort);
            $this->updateEnv('MAIL_USERNAME', $mailUsername);
            $this->updateEnv('MAIL_PASSWORD', $mailPassword);
            $this->updateEnv('MAIL_ENCRYPTION', $mailEncryption);
            $this->updateEnv('MAIL_FROM_ADDRESS', $mailFromAddress);

            $this->components->task('Configuring email delivery settings', fn () => true);

            $this->newLine();
            $this->components->info('✅ Email delivery configured successfully!');
        } else {
            $this->components->warn('Using log driver for emails. Messages will be written to the log file instead of being sent.');
            $this->updateEnv('MAIL_MAILER', 'log');
        }

        $this->newLine();
        if (confirm('Would you like to receive Discord notifications too?', false)) {
            $webhookUrl = text(
                label: 'Please paste your Discord webhook URL',
                required: true,
                hint: 'Paste your Discord webhook URL here.'
            );
            $this->updateEnv('DISCORD_WEBHOOK_URL', $webhookUrl);
            $this->components->task('Setting up Discord notifications', fn () => true);
            $this->components->info('✅ Discord notifications configured!');
        } else {
            $this->components->info('No problem! You can always add Discord notifications later.');
        }

        $this->newLine();
        if (confirm('Would you like to specify a URL to send POST request notifications to?', false)) {
            $POSTWebhookURL = text(
                label: 'Please specify the URL',
                required: true,
                hint: 'The URL to which POST requests will be sent.'
            );
            $this->updateEnv('WEBHOOK_POST_URL', $POSTWebhookURL);
            $this->components->task('Setting up POST Webhook notifications', fn () => true);
            $this->components->info('✅ POST Webhook notifications configured!');
        } else {
            $this->components->info('No problem! You can always configure this later.');
        }

        $this->updateEnv('LOG_LEVEL', 'info');

        $this->newLine();
        $this->components->info('✨ Configuration complete! Your settings have been saved.');
    }

    /**
     * Update the .env file with new values.
     */
    private function updateEnv(string $key, ?string $value): void
    {
        if (is_null($value)) {
            $value = 'null';
        } else {
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

        File::put($envFile, (string) $content);
    }

    /**
     * Show next steps to the user.
     */
    private function showNextSteps(): void
    {
        $this->components->info('🚀 Ready to start monitoring? Here\'s what to do next:');
        $this->newLine();

        $this->components->bulletList([
            'Add your first YouTube channel: <fg=yellow>php artisan channels:add</>',
            'Set up the scheduler (so checks run automatically): <fg=yellow>* * * * * cd '.base_path().' && php artisan schedule:run >> /dev/null 2>&1</>',
            'Test the system right now: <fg=yellow>php artisan channels:check</>',
            'View all monitored channels: <fg=yellow>php artisan channels:list</>',
        ]);

        $this->newLine();
        $this->components->info('Thank you for installing YouTube Channel Notifier!');

        $this->newLine(2);
        $this->components->twoColumnDetail(
            '<fg=bright-blue>⭐ Enjoying this tool?</>',
            'Please consider starring the project on GitHub: <fg=green>https://github.com/lewislarsen/youtube-channel-notifier</>'
        );
        $this->newLine();
    }
}
