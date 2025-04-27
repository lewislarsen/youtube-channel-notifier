# YouTube Channel Notifier (YCN) Project

> A lightweight Laravel application that keeps you connected with your favourite YouTube creators by delivering friendly notifications when new videos are published.


<div align="center">
  <img src="/website.png" alt="Image of the web page" width="800" height="auto">
</div>


<p align="center">
  <a href="#introduction">Introduction</a> •
  <a href="#key-features">Key Features</a> •
  <a href="#docker-installation">Quick Start with Docker</a> •
  <a href="#installation">Standard Installation</a> •
  <a href="#usage">Using the Notifier</a> •
  <a href="#configuration">Configuration Options</a> •
  <a href="#faq">FAQ</a> •
  <a href="#support">Support & Community</a> •
  <a href="#testing">Testing</a>
</p>

## Introduction

YouTube Channel Notifier (YCN) is a small web application that keeps track of your favourite YouTube creators and notifies you when they publish new videos. It is intended to be a robust little tool for individuals who still want to watch videos from their favourite creators but are unable or unwilling to have a Google account.


## Key Features

- **Effortless Channel Tracking**: Easily add your favourite YouTubers
- **Flexible Notifications**: Can deliver notifications via email or discord
- **Simple CLI Interface**: Manage everything through intuitive commands in your terminal
- **Privacy-Focused**: No YouTube API keys required, no data sharing with third parties
- **Docker Ready**: Get up and running in minutes with Docker support

> [!IMPORTANT]  
> This project is managed through simple CLI terminal commands and may not be suitable for everybody.

## Quick Start with Docker

The fastest way to get started:

### 1. Build the Docker image

```bash
./build-docker.sh
```

### 2. Run the container

```bash
docker run -d --name youtube-notifier \
  -p 8080:80 \
  -e MAIL_HOST=your-smtp-server \
  -e MAIL_PORT=587 \
  -e MAIL_USERNAME=your-username \
  -e MAIL_PASSWORD=your-password \
  -e MAIL_FROM_ADDRESS=your@email.com \
  -e ALERT_EMAILS=recipient1@email.com,recipient2@email.com \
  -e DISCORD_WEBHOOK_URL=your-discord-webhook-url \
  youtube-channel-notifier
```

### 3. Add your favorite channels

```bash
# Connect to the container
docker exec -it youtube-notifier sh

# Add YouTube channels to monitor
php artisan channels:add

# See what you're tracking
php artisan channels:list

# Exit when done (the notifier keeps running)
exit
```

That's it! The container automatically checks for new videos every 5 minutes and will notify you when creators post new content.

### Docker Container Management

```bash
# View container logs
docker logs youtube-notifier

# Stop the container
docker stop youtube-notifier

# Start the container
docker start youtube-notifier

# Remove the container
docker rm youtube-notifier
```

## Using the Notifier

YouTube Channel Notifier comes with friendly commands to manage everything:

### Channel Management

```bash
# Add a new YouTube channel to monitor
php artisan channels:add

# List all your monitored channels
php artisan channels:list

# Remove a channel you no longer want to track
php artisan channels:remove

# Mute a channel to stop receiving notifications
php artisan channels:mute

# Rename an existing channel you've added!
php artisan channels:rename

# View statistics about your channels
php artisan other:stats

# Sends a test email to all `ALERT_EMAILS` set in the .ENV. 
php artisan other:mail-test
```

### Video Management

```bash
# See all videos the notifier has discovered
php artisan videos:list

# Manually check your channels for new videos
php artisan channels:check
```

### Finding a YouTube Channel ID Manually

**This is only necessary if the automatic channel ID extraction fails!**

When adding a channel, we try our best to automatically determine the ID from their @youtubehandle, however if this fails, here's how to do it manually:

1. Visit the YouTube channel's page
2. View the page source (right-click → View Page Source)
3. Search for `itemprop="identifier" content="`
4. The ID appears after this text as a long string.

If you want to make sure this is correct, visit https://www.youtube.com/channel/ (with their ID appended) in your browser and see if it loads their page.

## Standard Installation

### Requirements

- PHP 8.2+
- Composer
- SQLite, PostgreSQL or MySQL

### Step-by-Step Installation

```bash
# Clone the repository
git clone https://github.com/lewislarsen/youtube-channel-notifier.git
cd youtube-channel-notifier

# Install dependencies
composer install

# Run our friendly interactive installer
php artisan app:install
```

The interactive installer will guide you through:
- Setting up your notification preferences
- Adding your first YouTube channels to monitor

### Manual Installation

If you prefer to configure things yourself:

```bash
# Copy environment file and generate application key
cp .env.example .env
php artisan key:generate

# Configure your database in .env, then run migrations
php artisan migrate

# Add notification settings to .env (see Configuration section)
```

### Scheduler Setup

To automatically check for new videos, add this cron entry to your server:

```bash
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

Note: If you're using Docker, the scheduler is already configured and running for you.

## Configuration Options

### Notification Settings

Configure your preferred notification methods in the `.env` file:

#### Email Notifications

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.example.com
MAIL_PORT=587
MAIL_USERNAME=username
MAIL_PASSWORD=password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=from@example.com

# Single or multiple recipients (comma-separated)
ALERT_EMAILS=your@email.com,another@email.com
```

#### Discord Notifications

```env
DISCORD_WEBHOOK_URL=https://discord.com/api/webhooks/your-webhook-url
```

### Video Filtering

The project automatically attempts to filters out certain types of content by default. This is configured in `config/excluded-video-words.php`:

```php
<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Video Filtering Configuration
    |--------------------------------------------------------------------------
    |
    | This configuration holds settings for filtering out unwanted videos.
    | Any video with a title containing these terms will be excluded
    | from import and notifications to reduce alert noise.
    |
    */

    'skip_terms' => [
        'live',
        'LIVE',
        'premiere',
        'trailer',
        'teaser',
        'preview',
    ],

];
```

By default, the application filters out:
- Livestreams (containing "live" or "LIVE")
- Premieres
- Trailers
- Teasers
- Previews

You can customize this list by editing the configuration file.

## FAQ

### Does this application download or store videos?
Not at all! We only track metadata (title, publish date, URL) about videos through public RSS feeds. No video content is ever downloaded, stored, or processed.

### How often does it check for new videos?
The notifier checks every 5 minutes, so you'll know about new content shortly after it's published.

### Does this use the YouTube API?
No, and that's a good thing! The application uses YouTube's public RSS feeds, which means:
- No API key required
- No quotas or rate limits to worry about
- Simpler setup for you

### Does it work with private/unlisted videos?
No, we can only detect publicly available videos that appear in the channel's public RSS feed.

### Can I get notifications for livestreams?
By default, we attempt to filter out livestreams. If you want to receive notifications for (likely) livestreams, you can edit the `config/excluded-video-words.php` file and remove 'live' and 'LIVE' from the skip terms.

### Does it support platforms other than YouTube?
There are no plans to support platforms other than YouTube.

### How can I request a new notification channel?
Please create an issue in the repository and I'll get back to you. 

## Support & Community

If you encounter any issues or have ideas for improvements, please open an issue.

## Contributing

Contributions are welcomed! If you'd like to help improve the project, please feel free to submit a Pull Request, and I'll give it a read as soon as I can.

I ask that any PRs have tests included (where relevant). We want to make sure this tool is reliable.

## Testing

This project uses [Pest](https://pestphp.com) for testing:

```bash
# Run all tests
./vendor/bin/pest
```
