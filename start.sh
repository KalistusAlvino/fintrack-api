echo "DB CONNECTION: $DB_CONNECTION"

php artisan vendor:publish --tag=passport-config
php artisan passport:keys --force
php artisan passport:client --personal --name=fintrack --provider=0 --no-interaction
php artisan passport:client --password --name=fintrack --provider=0 --no-interaction
php artisan serve --host=0.0.0.0 --port=8000 &

php artisan queue:work
