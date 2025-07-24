FROM php:8.3.22-cli

RUN apt-get update && apt-get install -y \
    git unzip curl libzip-dev libpng-dev libonig-dev libxml2-dev

WORKDIR /app

COPY composer.json package.json ./

COPY .env.example .env

COPY . .

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN composer install --no-interaction --prefer-dist --optimize-autoloader


RUN chown -R www-data:www-data storage bootstrap/cache && chmod -R 775 storage bootstrap/cache

RUN php artisan  storage:link

EXPOSE 8000

CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
CMD ["php", "artisan", "queue:work"]
