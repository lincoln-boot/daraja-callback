# Use the official PHP image with Apache
FROM php:8.1-apache

# Enable mod_rewrite (optional)
RUN a2enmod rewrite

# Copy all files to the Apache server root
COPY . /var/www/html/

# Set permissions
RUN chown -R www-data:www-data /var/www/html

EXPOSE 80

