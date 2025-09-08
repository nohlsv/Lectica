#!/bin/bash

npm run build

php artisan optimize:clear
php artisan optimize


systemctl restart laravel-reverb
systemctl restart laravel-queue

echo "Deployment complete!"
