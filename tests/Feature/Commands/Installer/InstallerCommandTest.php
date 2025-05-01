<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

beforeEach(function (): void {
    // Backup .env file if it exists
    if (File::exists(base_path('.env'))) {
        File::copy(base_path('.env'), base_path('.env.backup'));
    }

    // Ensure we have an .env.example file
    if (! File::exists(base_path('.env.example'))) {
        File::copy(base_path('.env.testing'), base_path('.env.example'));
    }
});

afterEach(function (): void {
    // Restore .env file from backup
    if (File::exists(base_path('.env.backup'))) {
        File::copy(base_path('.env.backup'), base_path('.env'));
        File::delete(base_path('.env.backup'));
    } elseif (File::exists(base_path('.env'))) {
        File::delete(base_path('.env'));
    }
});

it('has the install command registered', function (): void {
    expect(array_key_exists('app:install', Artisan::all()))->toBeTrue();
});

it('creates an env file with basic settings', function (): void {
    // Remove .env file if it exists
    if (File::exists(base_path('.env'))) {
        File::delete(base_path('.env'));
    }

    $this->artisan('app:install')
        ->expectsQuestion('Where should notifications be sent? (Email addresses, comma-separated for multiple)', 'test@example.com')
        ->expectsConfirmation('Would you like to configure SMTP for sending emails? (Recommended)', 'no')
        ->expectsConfirmation('Would you like to receive Discord notifications too?', 'no')
        ->expectsConfirmation('Would you like to specify a URL to send POST request notifications to?', 'no');

    expect(File::exists(base_path('.env')))->toBeTrue();
    $envContent = File::get(base_path('.env'));

    expect($envContent)->toContain('ALERT_EMAILS=test@example.com')
        ->toContain('MAIL_MAILER=log')
        ->toContain('LOG_LEVEL=info');
});

it('configures multiple email addresses correctly', function (): void {
    if (File::exists(base_path('.env'))) {
        File::delete(base_path('.env'));
    }

    $this->artisan('app:install')
        ->expectsQuestion('Where should notifications be sent? (Email addresses, comma-separated for multiple)', 'test1@example.com,test2@example.com')
        ->expectsConfirmation('Would you like to configure SMTP for sending emails? (Recommended)', 'no')
        ->expectsConfirmation('Would you like to receive Discord notifications too?', 'no')
        ->expectsConfirmation('Would you like to specify a URL to send POST request notifications to?', 'no');

    $envContent = File::get(base_path('.env'));

    expect($envContent)->toContain('ALERT_EMAILS=test1@example.com,test2@example.com');
});

it('configures SMTP settings correctly', function (): void {
    if (File::exists(base_path('.env'))) {
        File::delete(base_path('.env'));
    }

    $this->artisan('app:install')
        ->expectsQuestion('Where should notifications be sent? (Email addresses, comma-separated for multiple)', 'test@example.com')
        ->expectsConfirmation('Would you like to configure SMTP for sending emails? (Recommended)', 'yes')
        ->expectsQuestion('SMTP Host', 'smtp.example.com')
        ->expectsQuestion('SMTP Port', '587')
        ->expectsQuestion('SMTP Username (usually your email address)', 'user@example.com')
        ->expectsQuestion('SMTP Password (input will be hidden)', 'password123')
        ->expectsChoice('SMTP Encryption Type', 'tls', ['tls', 'ssl', 'none'])
        ->expectsQuestion('From Email Address', 'noreply@example.com')
        ->expectsConfirmation('Would you like to receive Discord notifications too?', 'no')
        ->expectsConfirmation('Would you like to specify a URL to send POST request notifications to?', 'no');

    $envContent = File::get(base_path('.env'));

    expect($envContent)
        ->toContain('MAIL_MAILER=smtp')
        ->toContain('MAIL_HOST=smtp.example.com')
        ->toContain('MAIL_PORT=587')
        ->toContain('MAIL_USERNAME=user@example.com')
        ->toContain('MAIL_PASSWORD=password123')
        ->toContain('MAIL_ENCRYPTION=tls')
        ->toContain('MAIL_FROM_ADDRESS=noreply@example.com');
});

it('configures Discord webhook correctly', function (): void {
    if (File::exists(base_path('.env'))) {
        File::delete(base_path('.env'));
    }

    $this->artisan('app:install')
        ->expectsQuestion('Where should notifications be sent? (Email addresses, comma-separated for multiple)', 'test@example.com')
        ->expectsConfirmation('Would you like to configure SMTP for sending emails? (Recommended)', 'no')
        ->expectsConfirmation('Would you like to receive Discord notifications too?', 'yes')
        ->expectsQuestion('Please paste your Discord webhook URL', 'https://discord.com/api/webhooks/123456/abcdef')
        ->expectsConfirmation('Would you like to specify a URL to send POST request notifications to?', 'no');

    $envContent = File::get(base_path('.env'));

    expect($envContent)
        ->toContain('DISCORD_WEBHOOK_URL=https://discord.com/api/webhooks/123456/abcdef');
});

it('configures post webhook url correctly', function (): void {
    if (File::exists(base_path('.env'))) {
        File::delete(base_path('.env'));
    }

    $this->artisan('app:install')
        ->expectsQuestion('Where should notifications be sent? (Email addresses, comma-separated for multiple)', 'test@example.com')
        ->expectsConfirmation('Would you like to configure SMTP for sending emails? (Recommended)', 'no')
        ->expectsConfirmation('Would you like to receive Discord notifications too?', 'no')
        ->expectsConfirmation('Would you like to specify a URL to send POST request notifications to?', 'yes')
        ->expectsQuestion('Please specify the URL', 'https://example.com/webhook');
    $envContent = File::get(base_path('.env'));

    expect($envContent)
        ->toContain('WEBHOOK_POST_URL=https://example.com/webhook');
});

it('handles "no encryption" option correctly', function (): void {
    if (File::exists(base_path('.env'))) {
        File::delete(base_path('.env'));
    }

    $this->artisan('app:install')
        ->expectsQuestion('Where should notifications be sent? (Email addresses, comma-separated for multiple)', 'test@example.com')
        ->expectsConfirmation('Would you like to configure SMTP for sending emails? (Recommended)', 'yes')
        ->expectsQuestion('SMTP Host', 'smtp.example.com')
        ->expectsQuestion('SMTP Port', '25')
        ->expectsQuestion('SMTP Username (usually your email address)', 'user@example.com')
        ->expectsQuestion('SMTP Password (input will be hidden)', 'password123')
        ->expectsChoice('SMTP Encryption Type', 'none', ['tls', 'ssl', 'none'])
        ->expectsQuestion('From Email Address', 'noreply@example.com')
        ->expectsConfirmation('Would you like to receive Discord notifications too?', 'no')
        ->expectsConfirmation('Would you like to specify a URL to send POST request notifications to?', 'no');

    $envContent = File::get(base_path('.env'));

    expect($envContent)
        ->toContain('MAIL_ENCRYPTION=null');
});

it('aborts installation when user chooses not to overwrite existing .env', function (): void {
    // Create a dummy .env file
    File::put(base_path('.env'), 'DUMMY=value');

    $this->artisan('app:install')
        ->expectsConfirmation('I noticed an .env file already exists. Is it okay to replace it?', 'no');

    // Verify the file wasn't modified
    expect(File::get(base_path('.env')))->toBe('DUMMY=value');
});

it('properly handles values with spaces in env file', function (): void {
    if (File::exists(base_path('.env'))) {
        File::delete(base_path('.env'));
    }

    $this->artisan('app:install')
        ->expectsQuestion('Where should notifications be sent? (Email addresses, comma-separated for multiple)', 'test@example.com')
        ->expectsConfirmation('Would you like to configure SMTP for sending emails? (Recommended)', 'yes')
        ->expectsQuestion('SMTP Host', 'smtp.example.com')
        ->expectsQuestion('SMTP Port', '587')
        ->expectsQuestion('SMTP Username (usually your email address)', 'user@example.com')
        ->expectsQuestion('SMTP Password (input will be hidden)', 'password with spaces')
        ->expectsChoice('SMTP Encryption Type', 'tls', ['tls', 'ssl', 'none'])
        ->expectsQuestion('From Email Address', 'noreply@example.com')
        ->expectsConfirmation('Would you like to receive Discord notifications too?', 'no')
        ->expectsConfirmation('Would you like to specify a URL to send POST request notifications to?', 'no');

    $envContent = File::get(base_path('.env'));

    expect($envContent)
        ->toContain('MAIL_PASSWORD="password with spaces"');
});
