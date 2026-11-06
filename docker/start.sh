#!/bin/sh
set -e

php artisan optimize:clear
php artisan migrate --force --seed
php artisan config:cache
php artisan route:cache
php artisan view:cache

exec apache2-foreground
