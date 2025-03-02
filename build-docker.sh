#!/bin/bash

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo -e "${BLUE}YouTube Channel Notifier - Docker Setup${NC}"
echo "Building a container for the YCN..."

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

# Ensure we have a .env file
if [ ! -f /app/.env ]; then
    echo "Creating default .env file"
    cp /app/.env.example /app/.env || echo "APP_KEY=" > /app/.env
fi

# Update .env with Docker environment variables
if [ ! -z "$MAIL_HOST" ]; then
    sed -i "s/^MAIL_HOST=.*/MAIL_HOST=${MAIL_HOST}/" /app/.env 2>/dev/null || echo "MAIL_HOST=${MAIL_HOST}" >> /app/.env
fi

if [ ! -z "$MAIL_PORT" ]; then
    sed -i "s/^MAIL_PORT=.*/MAIL_PORT=${MAIL_PORT}/" /app/.env 2>/dev/null || echo "MAIL_PORT=${MAIL_PORT}" >> /app/.env
fi

if [ ! -z "$MAIL_USERNAME" ]; then
    sed -i "s/^MAIL_USERNAME=.*/MAIL_USERNAME=${MAIL_USERNAME}/" /app/.env 2>/dev/null || echo "MAIL_USERNAME=${MAIL_USERNAME}" >> /app/.env
fi

if [ ! -z "$MAIL_PASSWORD" ]; then
    sed -i "s/^MAIL_PASSWORD=.*/MAIL_PASSWORD=${MAIL_PASSWORD}/" /app/.env 2>/dev/null || echo "MAIL_PASSWORD=${MAIL_PASSWORD}" >> /app/.env
fi

if [ ! -z "$MAIL_ENCRYPTION" ]; then
    sed -i "s/^MAIL_ENCRYPTION=.*/MAIL_ENCRYPTION=${MAIL_ENCRYPTION}/" /app/.env 2>/dev/null || echo "MAIL_ENCRYPTION=${MAIL_ENCRYPTION}" >> /app/.env
fi

if [ ! -z "$MAIL_FROM_ADDRESS" ]; then
    sed -i "s/^MAIL_FROM_ADDRESS=.*/MAIL_FROM_ADDRESS=${MAIL_FROM_ADDRESS}/" /app/.env 2>/dev/null || echo "MAIL_FROM_ADDRESS=${MAIL_FROM_ADDRESS}" >> /app/.env
fi

if [ ! -z "$ALERT_EMAILS" ]; then
    sed -i "s/^ALERT_EMAILS=.*/ALERT_EMAILS=${ALERT_EMAILS}/" /app/.env 2>/dev/null || echo "ALERT_EMAILS=${ALERT_EMAILS}" >> /app/.env
fi

if [ ! -z "$DISCORD_WEBHOOK_URL" ]; then
    sed -i "s/^DISCORD_WEBHOOK_URL=.*/DISCORD_WEBHOOK_URL=${DISCORD_WEBHOOK_URL}/" /app/.env 2>/dev/null || echo "DISCORD_WEBHOOK_URL=${DISCORD_WEBHOOK_URL}" >> /app/.env
fi

# Always generate app key if not set
if ! grep -q "^APP_KEY=[a-zA-Z0-9:+=/]\+$" /app/.env; then
    echo "Generating application key"
    php artisan key:generate --no-interaction --force
fi

# Run artisan optimize (which includes config:cache, route:cache, view:cache)
echo "Optimizing application"
php artisan optimize --no-interaction

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

echo "Building image..."

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
    echo -e "${BLUE}Quick Management Commands:${NC}"
    echo -e "1. Connect to the container:"
    echo -e "   docker exec -it youtube-notifier sh"
    echo -e ""
    echo -e "2. Add your first channel:"
    echo -e "   php artisan channels:add"
    echo -e ""
    echo -e "See README.md for complete usage instructions."
    echo ""
    echo -e "${YELLOW}Find this useful? Star the repo: github.com/lewislarsen/youtube-channel-notifier${NC}"

    # Remove log file on success
    rm docker-build.log 2>/dev/null
    exit 0
else
    echo -e "${RED}❌ Build failed${NC}"
    echo "Build log saved to docker-build.log for troubleshooting."
    echo "If you need help, please open an issue on GitHub."
    exit 1
fi
