# YouTube Channel Notifier

This project is a Channels Management System that checks YouTube channels for new videos and sends notifications when new videos are found. It includes commands to add new channels and to check all channels for updates.

## Features

- **Check Channels:** Check all YouTube channels for new videos and send notifications.
- **Add Channel:** Add a new YouTube channel and perform an initial video import.
- **Email Notifications:** Send email notifications when new videos are found.

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

   Copy the `.env.example` file to `.env` and configure your database and mail settings.

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

6. **Run Laravel Pint for code formatting:**

   ```sh
   ./vendor/bin/pint
   ```

## Usage

### Commands

1. **Check Channels:**

   Check all channels for new videos and send notifications if necessary.

   ```sh
   php artisan channel:check
   ```

2. **Add Channel:**

   Add a new YouTube channel and perform an initial video import.

   ```sh
   php artisan channel:add
   ```

### Running Tests

To run the tests, use the following command:

```sh
./vendor/bin/pest
```
