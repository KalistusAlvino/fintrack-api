#!/bin/sh
php artisan serve --host=0.0.0.0 --port=8000 &  # background
php artisan queue:work
