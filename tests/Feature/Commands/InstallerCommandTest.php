<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

beforeEach(function () {
    // Backup .env file if it exists
    if (File::exists(base_path('.env'))) {
        File::copy(base_path('.env'), base_path('.env.backup'));
    }

    // Ensure we have an .env.example file
    if (! File::exists(base_path('.env.example'))) {
        File::copy(base_path('.env.testing'), base_path('.env.example'));
    }
});

afterEach(function () {
    // Restore .env file from backup
    if (File::exists(base_path('.env.backup'))) {
        File::copy(base_path('.env.backup'), base_path('.env'));
        File::delete(base_path('.env.backup'));
    } elseif (File::exists(base_path('.env'))) {
        File::delete(base_path('.env'));
    }
});

it('has the install command registered', function () {
    expect(array_key_exists('app:install', Artisan::all()))->toBeTrue();
});

it('creates an env file with basic settings', function () {
    // Remove .env file if it exists
    if (File::exists(base_path('.env'))) {
        File::delete(base_path('.env'));
    }

    $this->artisan('app:install')
        ->expectsQuestion('Enter email address(es) for notifications (comma-separated for multiple)', 'test@example.com')
        ->expectsConfirmation('Do you want to configure SMTP for sending emails?', 'no')
        ->expectsConfirmation('Do you want to set up Discord notifications?', 'no')
        ->assertExitCode(0);

    expect(File::exists(base_path('.env')))->toBeTrue();
    $envContent = File::get(base_path('.env'));

    expect($envContent)->toContain('ALERT_EMAIL=test@example.com')
        ->toContain('MAIL_MAILER=log')
        ->toContain('LOG_LEVEL=info');
});

it('configures multiple email addresses correctly', function () {
    if (File::exists(base_path('.env'))) {
        File::delete(base_path('.env'));
    }

    $this->artisan('app:install')
        ->expectsQuestion('Enter email address(es) for notifications (comma-separated for multiple)', 'test1@example.com,test2@example.com')
        ->expectsConfirmation('Do you want to configure SMTP for sending emails?', 'no')
        ->expectsConfirmation('Do you want to set up Discord notifications?', 'no')
        ->assertExitCode(0);

    $envContent = File::get(base_path('.env'));

    expect($envContent)->toContain('ALERT_EMAIL=test1@example.com,test2@example.com');
});

it('configures SMTP settings correctly', function () {
    if (File::exists(base_path('.env'))) {
        File::delete(base_path('.env'));
    }

    $this->artisan('app:install')
        ->expectsQuestion('Enter email address(es) for notifications (comma-separated for multiple)', 'test@example.com')
        ->expectsConfirmation('Do you want to configure SMTP for sending emails?', 'yes')
        ->expectsQuestion('SMTP Host', 'smtp.example.com')
        ->expectsQuestion('SMTP Port', '587')
        ->expectsQuestion('SMTP Username', 'user@example.com')
        ->expectsQuestion('SMTP Password', 'password123')
        ->expectsChoice('SMTP Encryption', 'tls', ['tls', 'ssl', 'none'])
        ->expectsQuestion('From Email Address', 'noreply@example.com')
        ->expectsQuestion('From Name', 'Test Notifier')
        ->expectsConfirmation('Do you want to set up Discord notifications?', 'no')
        ->assertExitCode(0);

    $envContent = File::get(base_path('.env'));

    expect($envContent)
        ->toContain('MAIL_MAILER=smtp')
        ->toContain('MAIL_HOST=smtp.example.com')
        ->toContain('MAIL_PORT=587')
        ->toContain('MAIL_USERNAME=user@example.com')
        ->toContain('MAIL_PASSWORD=password123')
        ->toContain('MAIL_ENCRYPTION=tls')
        ->toContain('MAIL_FROM_ADDRESS=noreply@example.com')
        ->toContain('MAIL_FROM_NAME="Test Notifier"');
});

it('configures Discord webhook correctly', function () {
    if (File::exists(base_path('.env'))) {
        File::delete(base_path('.env'));
    }

    $this->artisan('app:install')
        ->expectsQuestion('Enter email address(es) for notifications (comma-separated for multiple)', 'test@example.com')
        ->expectsConfirmation('Do you want to configure SMTP for sending emails?', 'no')
        ->expectsConfirmation('Do you want to set up Discord notifications?', 'yes')
        ->expectsQuestion('Enter your Discord webhook URL', 'https://discord.com/api/webhooks/123456/abcdef')
        ->assertExitCode(0);

    $envContent = File::get(base_path('.env'));

    expect($envContent)
        ->toContain('DISCORD_WEBHOOK_URL=https://discord.com/api/webhooks/123456/abcdef');
});

it('handles "no encryption" option correctly', function () {
    if (File::exists(base_path('.env'))) {
        File::delete(base_path('.env'));
    }

    $this->artisan('app:install')
        ->expectsQuestion('Enter email address(es) for notifications (comma-separated for multiple)', 'test@example.com')
        ->expectsConfirmation('Do you want to configure SMTP for sending emails?', 'yes')
        ->expectsQuestion('SMTP Host', 'smtp.example.com')
        ->expectsQuestion('SMTP Port', '25')
        ->expectsQuestion('SMTP Username', 'user@example.com')
        ->expectsQuestion('SMTP Password', 'password123')
        ->expectsChoice('SMTP Encryption', 'none', ['tls', 'ssl', 'none'])
        ->expectsQuestion('From Email Address', 'noreply@example.com')
        ->expectsQuestion('From Name', 'Test Notifier')
        ->expectsConfirmation('Do you want to set up Discord notifications?', 'no')
        ->assertExitCode(0);

    $envContent = File::get(base_path('.env'));

    expect($envContent)
        ->toContain('MAIL_ENCRYPTION=null');
});

it('aborts installation when user chooses not to overwrite existing .env', function () {
    // Create a dummy .env file
    File::put(base_path('.env'), 'DUMMY=value');

    $this->artisan('app:install')
        ->expectsConfirmation('An .env file already exists. Do you want to overwrite it?', 'no')
        ->assertExitCode(1);

    // Verify the file wasn't modified
    expect(File::get(base_path('.env')))->toBe('DUMMY=value');
});

it('properly handles values with spaces in env file', function () {
    if (File::exists(base_path('.env'))) {
        File::delete(base_path('.env'));
    }

    $this->artisan('app:install')
        ->expectsQuestion('Enter email address(es) for notifications (comma-separated for multiple)', 'test@example.com')
        ->expectsConfirmation('Do you want to configure SMTP for sending emails?', 'yes')
        ->expectsQuestion('SMTP Host', 'smtp.example.com')
        ->expectsQuestion('SMTP Port', '587')
        ->expectsQuestion('SMTP Username', 'user@example.com')
        ->expectsQuestion('SMTP Password', 'password with spaces')
        ->expectsChoice('SMTP Encryption', 'tls', ['tls', 'ssl', 'none'])
        ->expectsQuestion('From Email Address', 'noreply@example.com')
        ->expectsQuestion('From Name', 'Company Name With Spaces')
        ->expectsConfirmation('Do you want to set up Discord notifications?', 'no')
        ->assertExitCode(0);

    $envContent = File::get(base_path('.env'));

    expect($envContent)
        ->toContain('MAIL_PASSWORD="password with spaces"')
        ->toContain('MAIL_FROM_NAME="Company Name With Spaces"');
});
