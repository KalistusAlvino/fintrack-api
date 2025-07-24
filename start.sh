#!/bin/sh

echo "Starting Laravel server..."
nohup php artisan serve --host=0.0.0.0 --port=10000 > /dev/null 2>&1 &

echo "Starting Laravel queue worker..."
php artisan queue:work
