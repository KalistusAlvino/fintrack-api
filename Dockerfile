FROM php:8.3.22-cli

RUN apt-get update && apt-get install -y \
    git unzip curl libzip-dev libpng-dev libonig-dev libxml2-dev

WORKDIR /app

COPY composer.json package.json ./


COPY . .

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN composer install --no-interaction --prefer-dist --optimize-autoloader

RUN docker-php-ext-install pdo_mysql

RUN chown -R www-data:www-data storage bootstrap/cache && chmod -R 775 storage bootstrap/cache


RUN php artisan storage:link

RUN php artisan config:clear

EXPOSE 8000

COPY start.sh /start.sh
RUN chmod +x /start.sh
CMD ["/start.sh"]
