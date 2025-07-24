#!/bin/sh
php artisan serve --host=0.0.0.0 --port=10000 &  # background
php artisan queue:work
