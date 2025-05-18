FROM php:8.1-apache

# Enable mod_rewrite for Laravel or clean URLs
RUN a2enmod rewrite

# Set the working directory
WORKDIR /var/www/html

# Copy all files into the container
COPY . .

# Set permissions (optional but recommended)
RUN chown -R www-data:www-data /var/www/html
