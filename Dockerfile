FROM php:8.2-apache

RUN apt-get update && apt-get install -y git unzip

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY . .

RUN composer install --no-dev --no-interaction --prefer-dist

EXPOSE 80

CMD ["php", "-S", "0.0.0.0:80", "-t", "public"]