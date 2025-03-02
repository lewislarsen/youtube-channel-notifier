# YouTube Channel Notifier

> A lightweight Laravel application that monitors your favourite YouTube channels and delivers timely notifications when new videos are published.


<div align="center">
  <img src="/website.png" alt="Image of the web page" width="800" height="auto">
</div>


<p align="center">
  <a href="#introduction">Introduction</a> •
  <a href="#key-features">Key Features</a> •
  <a href="#requirements">Requirements</a> •
  <a href="#installation">Installation</a> •
  <a href="#docker-installation">Docker Installation</a> •
  <a href="#configuration">Configuration</a> •
  <a href="#usage">Usage</a> •
  <a href="#faq">FAQ</a> •
  <a href="#testing">Testing</a> •
  <a href="#support">Support</a>
</p>

## Introduction

YouTube Channel Notifier is a solution for receiving notifications when YouTube creators post new content, intended primarily for people who don't have a Google Account any more and are unable to have subscriptions.

## Key Features

- **Channel Tracking**: Easily add YouTube channels you want to monitor
- **Notifications**: Receive alerts through email, Discord, or both when new videos are published
- **Simple CLI Interface**: Manage everything through intuitive commands
- **Privacy Focused**: No YouTube API keys required, no data sharing with third parties
- **Docker Support**: Run in a container with automatic setup and persistence

> [!IMPORTANT]  
> This project is managed through (simple) CLI terminal commands and has no web interface for management.

## Requirements

- PHP 8.2+
- Composer
- SQLite, Postgresql or MySQL

For Docker installation:
- Docker

## Installation

### Quick Start

```bash
# Clone the repository
git clone https://github.com/lewislarsen/youtube-channel-notifier.git
cd youtube-channel-notifier

# Install dependencies
composer install

# Run the interactive installer
php artisan app:install
```

The interactive installer will guide you through:
- Setting up notification preferences
- Adding your first YouTube channels

### Manual Installation

If you prefer to configure manually:

```bash
# Copy environment file and generate application key
cp .env.example .env
php artisan key:generate

# Configure your database in .env, then run migrations
php artisan migrate

# Add notification settings to .env (see Configuration section)
```

## Docker Installation

For the simplest possible setup, you can run YouTube Channel Notifier using Docker:

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

### 3. Manage channels from within the container

```bash
# 1. Connect to the container
docker exec -it youtube-notifier sh

# 2. Run these commands inside the container:
php artisan channels:add     # Add a new YouTube channel
php artisan channels:list    # List all monitored channels
php artisan channels:remove  # Remove a channel
php artisan channels:check   # Manually check for new videos
php artisan videos:list      # List discovered videos

# 3. Type 'exit' and press Enter to leave the container
# The container will continue running in the background
```

Docker automatically handles data persistence and the scheduler, so you don't need to configure a cron job.

## Configuration

### Scheduler Setup

To automate checks for new videos, add this single cron entry to your server:

```bash
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

Note: If using Docker, the scheduler is already configured and running.

### Notification Configuration

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

## Usage

YouTube Channel Notifier provides a suite of intuitive Artisan commands:

### Channel Management

```bash
# Add a new YouTube channel to monitor
php artisan channels:add

# List all monitored channels
php artisan channels:list

# Remove a channel from monitoring
php artisan channels:remove
```

### Finding a YouTube Channel ID

> [!NOTE]  
> This is a clumsy way to do it but it works for the time being. Any efforts that could be made towards 'extracting' the identifier through the page automatically would be welcomed.

When adding a channel, you'll need the channel ID. To find it:

1. Visit the YouTube channel's page
2. View the page source (right-click → View Page Source)
3. Search for `itemprop="identifier" content="`
4. The ID appears after this text

### Video Management

```bash
# List all videos discovered by the notifier
php artisan videos:list

# Manually check all channels for new videos
php artisan channels:check
```
## FAQ

### Does this application download or store videos?
No. YouTube Channel Notifier only tracks metadata (title, publish date, URL) about videos through RSS feeds. No video content is ever downloaded, stored, or processed.

### Does it support platforms other than YouTube?
It does not. Currently, the application is designed specifically to read YouTube's RSS data.

### How often does it check for new videos?
The project checks every 5 minutes.

### Does this use the YouTube API?
No. The application uses YouTube's public RSS feeds, which means:
- No API key required
- No quotas or rate limits to worry about

### Can I get notifications for livestreams?
We do try to strip out live video content currently as it isn't the intended target for this project.

### Does it work with private/unlisted videos?
No. Only publicly available videos that appear in the channel's RSS feed can be detected.

### How can I get an additional notification channel added?
Make an issue in the repository and I'll review it. I don't want to add too many, but I'd like there to be options for people.

## Testing

This project uses [Pest](https://pestphp.com) for testing:

```bash
# Run all tests
./vendor/bin/pest
```

## Support

If you encounter any issues or have feature requests please open an issue on this repository.

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request and I'll check it out.
