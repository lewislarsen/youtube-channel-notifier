# YouTube Channel Notifier

The YouTube Channel Notifier is a simple tool for managing and monitoring YouTube channels. It periodically checks RSS feeds for new videos and sends notifications when updates are detected. This project is managed exclusively via Terminal console commands, with no web interface.

## Features

- **Check Channels:** Check all YouTube channels for new videos and send notifications.
- **Email Notifications:** Send email notifications when new videos are found.
- **Discord Notifications:** Send Discord webhook notifications when new videos are found.

## Requirements

- PHP 8.3 or higher
- Composer

## Installation

1. **Clone the repository:**

   ```sh
   git clone https://github.com/lewislarsen/youtube-channel-notifier.git
   cd youtube-channel-notifier
   ```

2. **Install dependencies:**

   ```sh
   composer install
   ```

3. **Set up your environment:**

   Copy the `.env.example` file to `.env` and configure your database and notification settings:

    - `ALERT_EMAIL`: The email address where alerts will be sent
    - `DISCORD_WEBHOOK_URL`: Your Discord webhook URL for sending notifications to a Discord channel

   ```sh
   cp .env.example .env
   ```

4. **Generate an application key:**

   ```sh
   php artisan key:generate
   ```

5. **Run the database migrations:**

   ```sh
   php artisan migrate
   ```

6. **Start the Artisan Scheduler:**

Ensure the PHP Artisan scheduler is running. This requires setting up a cron job to execute the `php artisan schedule:run` command every minute.

Example cron configuration:

   ```sh
* * * * * /usr/bin/php /path-to-your-project/artisan schedule:run >> /dev/null 2>&1
   ```


## Usage

### Commands

**Add a Channel:**

Add a new YouTube channel to the notifier and automatically import its existing video history.

To find the **Channel ID**, follow these steps:
1. Visit the YouTube channel's page in a browser.
2. Right-click anywhere on the page and select "View Page Source."
3. Search for the term `itemprop="identifier" content="` in the source code.
4. The Channel ID is the value inside the `content=""` attribute next to this term.

Once you have the Channel ID, add it using the following command:

```sh
php artisan channels:add
```

**Remove a Channel:**

Remove a previously added YouTube channel:

   ```sh
   php artisan channels:remove
   ```

**Lists all Channels:**

View a list of all YouTube channels being monitored.

   ```sh
   php artisan channels:list
   ```

**Lists all Videos:**

View all videos fetched from monitored channels:

   ```sh
   php artisan videos:list
   ```

### Notifications

The project supports two notification methods:

1. **Email**: Configure your SMTP settings in the `.env` file and set the `ALERT_EMAIL` variable to receive email alerts.

2. **Discord**: Set a webhook in your Discord and add the webhook URL to the `DISCORD_WEBHOOK_URL` variable in the `.env` file.

### Running Tests

Run the test suite using Pest:

```sh
./vendor/bin/pest
```

## Troubleshooting

If you encounter any issues, feel free to open an issue in the repository. I'll address it as soon as possible.
