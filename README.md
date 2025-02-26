# YouTube Channel Notifier

A small, Laravel-powered app that keeps track of YouTube channels and notifies you of new uploads.

<p align="center">
  <a href="#requirements">Requirements</a> |
  <a href="#installation">Installation</a> |
  <a href="#configuration">Configuration</a> |
  <a href="#usage">Usage</a> |
  <a href="#notifications">Notifications</a> |
  <a href="#testing">Testing</a> |
  <a href="#support">Support</a>
</p>

## Introduction

YouTube Channel Notifier elegantly tracks your favourite YouTube channels via their RSS feeds and delivers timely notifications when new content is published. This console-based application provides a simple, powerful way to stay updated without checking YouTube.

## Requirements

- PHP 8.3+
- Composer

## Installation

First, install the YouTube Channel Notifier using Composer:

```bash
git clone https://github.com/lewislarsen/youtube-channel-notifier.git
cd youtube-channel-notifier
```

Once installed, run the convenient installation command:

```bash
php artisan app:install
```

This interactive installer will guide you through configuring notification methods, and preparing your database.

## Configuration

### Scheduler

To automate checking for new videos, add the scheduler to your server's crontab:

```bash
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

### Manual Configuration

If you prefer manual configuration:

```bash
cp .env.example .env
php artisan key:generate
php artisan migrate
```

Edit your `.env` file to configure:

```
ALERT_EMAILS=your@email.com
DISCORD_WEBHOOK_URL=https://discord.com/api/webhooks/...
```

## Usage

YouTube Channel Notifier provides a suite of Artisan commands for managing your channels:

### Adding Channels

```bash
php artisan channels:add
```

This command will prompt you for a YouTube Channel ID. To locate a Channel ID:

1. Visit the channel on YouTube
2. View page source (right-click → View Page Source)
3. Search for `itemprop="identifier" content="`
4. The ID appears within this content attribute

### Managing Channels

```bash
# List all monitored channels
php artisan channels:list

# Remove a channel
php artisan channels:remove

# List all videos
php artisan videos:list

# Manually check for new videos
php artisan channels:check
```

## Notifications

YouTube Channel Notifier supports two notification methods that can be configured in your `.env` file:

### Email

Configure your mail settings and recipients:

```
MAIL_MAILER=smtp
MAIL_HOST=smtp.example.com
MAIL_PORT=587
MAIL_USERNAME=username
MAIL_PASSWORD=password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=from@example.com

# Single recipient
ALERT_EMAILS=recipient@example.com

# Multiple recipients
ALERT_EMAILS=first@example.com,second@example.com
```

### Discord

Add your Discord webhook URL to receive notifications directly in your server:

```
DISCORD_WEBHOOK_URL=https://discord.com/api/webhooks/your-webhook-url
```

## Testing

YouTube Channel Notifier uses [Pest](https://pestphp.com) for testing:

```bash
./vendor/bin/pest
```

## Support

If you discover a bug or have a feature request, please open an issue on GitHub.

<p align="center">
  Made with ❤️ by <a href="https://github.com/lewislarsen">Lewis Larsen</a>
</p>
