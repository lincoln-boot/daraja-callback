FROM php:8.1-apache

RUN apt-get update && apt-get install -y libzip-dev zip unzip && docker-php-ext-install mysqli

RUN a2enmod rewrite

WORKDIR /var/www/html

COPY . .

RUN chown -R www-data:www-data /var/www/html
