#!/bin/bash

# Production deployment script for Lectica
# Run this after git pull

echo "Starting production deployment..."

# 1. Install/update dependencies
composer install --no-dev --optimize-autoloader

# 2. Clear and cache configuration
php artisan config:clear
php artisan config:cache

# 3. Clear and cache routes
php artisan route:clear
php artisan route:cache

# 4. Clear and cache views
php artisan view:clear
php artisan view:cache

# 5. Run database migrations
php artisan migrate --force

# 6. Clear application cache
php artisan cache:clear

# 7. Restart queue workers (if using queues)
# php artisan queue:restart
systemctl restart laravel-reverb
systemctl restart laravel-queue

# 8. Restart broadcasting server (for your multiplayer features)
# You might need to restart Reverb/Pusher workers here

# 9. Set proper permissions
chmod -R 755 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

echo "Deployment complete!"
echo "Don't forget to:"
echo "1. Restart your web server (nginx/apache)"
echo "2. Restart any background workers"
echo "3. Test critical features (email, multiplayer games)"
