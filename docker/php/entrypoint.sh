#!/bin/sh
set -e

# Path to the lock file that indicates that the initial setup is complete
INIT_LOCK_FILE="/var/www/html/storage/logs/.initialized"

# If the setup has already been completed, just execute the main command
if [ -f "$INIT_LOCK_FILE" ]; then
    echo "Setup already completed. Starting services..."
    exec "$@"
fi

# --- First-time setup ---
echo "Performing first-time setup. This will only run once."

# 1. Set correct permissions
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

# 2. Install Composer dependencies
echo "Installing composer dependencies..."
composer install --no-interaction --no-progress --prefer-dist

# 3. Create .env file
if [ ! -f ".env" ]; then
    if [ ! -f ".env.example" ]; then
      echo "ERROR: .env.example not found!"
      exit 1
    fi
    echo "Creating .env file from .env.example..."
    cp .env.example .env
fi

# 4. Generate app key
if [ -z "$(grep '^APP_KEY=' .env | cut -d '=' -f2-)" ]; then
    echo "Generating application key..."
    php artisan key:generate
fi

# 5. Create storage link
if [ ! -L "public/storage" ]; then
    echo "Creating storage link..."
    php artisan storage:link
fi

# 6. Wait for services to be ready
echo "Waiting for database connection..."
while ! nc -z pgsql 5432; do
  sleep 1
done
echo "Database is ready!"

echo "Waiting for RabbitMQ..."
while ! nc -z rabbitmq 5672; do
  sleep 1
done
echo "RabbitMQ is ready!"

# 7. Run database migrations
echo "Running database migrations and seeding..."
php artisan migrate:fresh --force

# 8. Install Node dependencies
if [ ! -d "node_modules" ]; then
  echo "Installing node dependencies..."
  yarn install
fi

# 9. Build frontend assets
if [ ! -d "public/build" ]; then
  echo "Building frontend assets..."
  yarn build
fi

# 10. Create the lock file
touch "$INIT_LOCK_FILE"

echo "Setup finished. Starting services..."
exec "$@"
