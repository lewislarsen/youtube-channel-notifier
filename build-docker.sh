#!/bin/bash

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo -e "${BLUE}YouTube Channel Notifier - Docker Setup${NC}"
echo "Building a container for the YCN."

# Check if Docker is installed and running
if ! command -v docker &> /dev/null || ! docker info &> /dev/null; then
    echo -e "${RED}Error: Docker is not installed or not running.${NC}"
    echo "Please start Docker and try again."
    exit 1
fi

# Create required directories quietly
mkdir -p docker &> /dev/null

# Create config files without showing output
cat > docker/nginx.conf << 'EOL'
server {
    listen 80;
    root /app/public;
    index index.php;
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    location ~ \.php$ {
        fastcgi_pass 127.0.0.1:9000;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
EOL

cat > docker/supervisord.conf << 'EOL'
[supervisord]
nodaemon=true
user=root
logfile=/dev/stdout
logfile_maxbytes=0
pidfile=/var/run/supervisord.pid

[program:php-fpm]
command=php-fpm -F
stdout_logfile=/dev/stdout
stdout_logfile_maxbytes=0
stderr_logfile=/dev/stderr
stderr_logfile_maxbytes=0
autorestart=true
startretries=0

[program:nginx]
command=nginx -g 'daemon off;'
stdout_logfile=/dev/stdout
stdout_logfile_maxbytes=0
stderr_logfile=/dev/stderr
stderr_logfile_maxbytes=0
autorestart=true
startretries=0

[program:laravel-scheduler]
command=sh -c "while true; do php /app/artisan schedule:run --verbose --no-interaction & sleep 60; done"
stdout_logfile=/dev/stdout
stdout_logfile_maxbytes=0
stderr_logfile=/dev/stderr
stderr_logfile_maxbytes=0
autorestart=true
startretries=0
EOL

cat > docker/entrypoint.sh << 'EOL'
#!/bin/sh

# Generate key if not exists (only if .env exists and APP_KEY is empty)
if [ -f /app/.env ] && ! grep -q "^APP_KEY=" /app/.env; then
    php artisan key:generate --no-interaction --force
fi

# Run migrations with tries to handle database availability delays
RETRY_COUNT=0
MAX_RETRIES=5
until php artisan migrate --no-interaction --force || [ $RETRY_COUNT -eq $MAX_RETRIES ]; do
    echo "Migration failed, retrying in 5 seconds..."
    RETRY_COUNT=$((RETRY_COUNT+1))
    sleep 5
done

# Run the installer if needed (first run)
if [ ! -f /app/.installed ]; then
    if [ "$SETUP_AUTOMATIC" = "true" ]; then
        touch /app/.installed
    else
        php artisan app:install
        touch /app/.installed
    fi
fi

# Start supervisord
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
EOL

# Make entrypoint executable
chmod +x docker/entrypoint.sh

echo "Configuring the YCN. Hold on!"

# Build Docker image with limited output
if docker build -t youtube-channel-notifier . > docker-build.log 2>&1; then
    echo -e "${GREEN}✅ Success! Your YouTube Channel Notifier is ready to deploy.${NC}"
    echo ""
    echo "Launch your notifier with:"
    echo ""
    echo "docker run -d --name youtube-notifier \\"
    echo "  -p 8080:80 \\"
    echo "  -e MAIL_HOST=smtp-server \\"
    echo "  -e MAIL_PORT=587 \\"
    echo "  -e MAIL_USERNAME=username \\"
    echo "  -e MAIL_PASSWORD=password \\"
    echo "  -e MAIL_FROM_ADDRESS=from@email.com \\"
    echo "  -e ALERT_EMAILS=email1@example.com,email2@example.com \\"
    echo "  -e DISCORD_WEBHOOK_URL=webhook-url \\"
    echo "  youtube-channel-notifier"
    echo ""
    echo -e "${BLUE}Manage your YouTube channels with these commands:${NC}"
    echo -e "1. Connect to the container:"
    echo -e "   docker exec -it youtube-notifier sh"
    echo -e ""
    echo -e "2. Run one of these commands inside the container:"
    echo -e "   php artisan channels:add     # Add a new channel to monitor"
    echo -e "   php artisan channels:list    # List all monitored channels"
    echo -e "   php artisan channels:remove  # Remove a channel"
    echo -e "   php artisan videos:list      # List discovered videos"
    echo -e ""
    echo -e "3. When finished, type 'exit' and press Enter to leave the container shell."
    echo -e "   The container will continue running in the background."
    echo -e ""
    echo -e "The container automatically checks for new videos every 5 minutes."
    echo -e "If you have issues, check the logs with:"
    echo -e "docker exec -it youtube-notifier cat /app/storage/logs/laravel.log"
    echo -e ""
    echo -e "Never miss a YouTube upload again!"
    echo ""
    echo -e "${YELLOW}Did the YouTube Channel Notifier help you?${NC}"
    echo -e "Please consider starring the repository at:"
    echo -e "https://github.com/lewislarsen/youtube-channel-notifier"
    echo ""
    # Remove log file on success
    rm docker-build.log 2>/dev/null
    exit 0
else
    echo -e "${RED}❌ Build failed${NC}"
    echo "Build log saved to docker-build.log for troubleshooting."
    echo "If you need help, please open an issue at:"
    echo "https://github.com/lewislarsen/youtube-channel-notifier/issues/new"
    exit 1
fi
